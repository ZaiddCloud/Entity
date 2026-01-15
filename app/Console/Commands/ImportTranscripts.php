<?php

namespace App\Console\Commands;

use App\Models\Audio;
use App\Models\AudioSegment;
use App\Models\Video;
use App\Models\VideoSegment;
use App\Services\EntityContentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ZipArchive;

class ImportTranscripts extends Command
{
    protected $signature = 'media:import-transcripts {path? : Path to folder containing docx files}';
    protected $description = 'Parse docx transcripts and create media segments automatically';

    protected $contentService;

    public function __construct(EntityContentService $contentService)
    {
        parent::__construct();
        $this->contentService = $contentService;
    }

    public function handle()
    {
        $inputPath = $this->argument('path');

        // If no path provided, try default but warn
        if (!$inputPath) {
            $defaultPath = storage_path('app/transcripts');
            if (!File::exists($defaultPath)) {
                $this->error("No path provided and default directory '$defaultPath' does not exist.");
                return 1;
            }
            $inputPath = $defaultPath;
        }

        $files = [];

        if (File::isDirectory($inputPath)) {
            $this->info("Scanning directory: $inputPath");
            $files = File::files($inputPath);
        } elseif (File::isFile($inputPath)) {
            $this->info("Processing single file: $inputPath");
            $files = [new \SplFileInfo($inputPath)];
        } else {
            $this->error("Invalid path: $inputPath");
            return 1;
        }

        $count = 0;
        foreach ($files as $file) {
            if ($file->getExtension() !== 'docx')
                continue;

            $this->info("Processing: " . $file->getFilename());
            $this->processFile($file);
            $count++;
        }

        if ($count === 0) {
            $this->warn("No .docx files found in the specified path.");
        } else {
            $this->info("Completed processing $count files.");
        }
    }

    protected function processFile($file)
    {
        $text = $this->readDocx($file->getPathname());
        if (!$text) {
            $this->warn("   - Could not read text from file.");
            return;
        }

        // 1. Find Media Entities (Audio/Video) matching the filename
        $filename = pathinfo($file->getFilename(), PATHINFO_FILENAME);

        $mediaCollection = $this->findMediaEntities($filename);

        if ($mediaCollection->isEmpty()) {
            $this->warn("   - No matching media found for '$filename'");
            return;
        }

        $this->info("   - Found " . $mediaCollection->count() . " matching entities.");

        // 2. Parse Segments
        $segments = $this->parseSegments($text);

        if (empty($segments)) {
            $this->warn("   - No segments found in text.");
            return;
        }

        $this->info("   - Parsed " . count($segments) . " segments.");

        // 3. Store Segments for ALL matches
        foreach ($mediaCollection as $media) {
            /** @var \Illuminate\Database\Eloquent\Model $media */
            $this->info("   > Attaching to: {$media->title} ({$media->getTable()}: {$media->id})");
            $this->storeSegments($media, $segments);
        }
    }

    protected function findMediaEntities($filename)
    {
        $matches = collect();

        // Strategy 1: Extract Date Pattern (YYMMDD)
        if (preg_match('/^(\d{6})/', $filename, $m)) {
            $datePrefix = $m[1];

            $audios = Audio::where('file_path', 'LIKE', "%$datePrefix%")
                ->orWhere('title', 'LIKE', "%$datePrefix%")
                ->get();

            $videos = Video::where('file_path', 'LIKE', "%$datePrefix%")
                ->orWhere('title', 'LIKE', "%$datePrefix%")
                ->get();

            $matches = $matches->merge($audios)->merge($videos);

            if ($matches->isNotEmpty()) {
                return $matches->unique('id'); // Avoid duplicates if any
            }
        }

        // Strategy 2: Exact Containment (Fallback)
        $audios = Audio::where('title', 'LIKE', "%$filename%")
            ->orWhere('file_path', 'LIKE', "%$filename%")
            ->get();

        $videos = Video::where('title', 'LIKE', "%$filename%")
            ->orWhere('file_path', 'LIKE', "%$filename%")
            ->get();

        return $matches->merge($audios)->merge($videos)->unique('id');
    }

    protected function readDocx($filePath)
    {
        $content = '';
        $zip = new ZipArchive;

        if ($zip->open($filePath) === true) {
            // Find word/document.xml
            if (($index = $zip->locateName('word/document.xml')) !== false) {
                $xmlData = $zip->getFromIndex($index);
                $dom = new \DOMDocument;
                $dom->loadXML($xmlData, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
                $nodes = $dom->getElementsByTagName('p'); // Paragraphs

                foreach ($nodes as $node) {
                    $content .= $node->nodeValue . "\n";
                }
            }
            $zip->close();
        }
        return $content;
    }

    protected function parseSegments($text)
    {
        $lines = explode("\n", $text);
        $parsed = [];
        $currentSegment = null;

        foreach ($lines as $i => $line) {
            $line = trim($line);
            if (empty($line))
                continue;

            // 1. Normalize Time Format
            // Replace dots or commas used as separators in time-like patterns "12.30" or "04,20" => "12:30", "04:20"
            // Look for patterns like digit{1,2}[.,]digit{2}
            $normalizedLine = preg_replace('/(\d{1,2})[.,](\d{2})/', '$1:$2', $line);
            // Run again to handle chained separators like 1,20,30 => 1:20:30
            $normalizedLine = preg_replace('/(\d{1,2})[.,](\d{2})/', '$1:$2', $normalizedLine);

            // 2. Detect Timestamp
            // Supports: (04:23), (1:04:23), 04:23, 1:04:23
            // With or without parens, at start/end or alone
            if (preg_match('/\(?(\d{1,2}:\d{2}(?::\d{2})?)\)?/', $normalizedLine, $matches)) {
                $timeString = $matches[1];
                $seconds = $this->timeToSeconds($timeString);

                // Determine Title
                // Candidate 1: The text in the same line (excluding the time)
                $titleCandidate = trim(str_replace($matches[0], '', $normalizedLine));

                // Cleanup title
                $titleCandidate = trim(str_replace([':', '(', ')'], '', $titleCandidate));

                $title = $titleCandidate;

                // Candidate 2: Previous line (if current line title is empty or very short)
                if (mb_strlen($title) < 3 && isset($lines[$i - 1])) {
                    $prevLine = trim($lines[$i - 1]);
                    // Heuristic: If prev line is short (< 80 chars) it's likely a speaker name
                    if (mb_strlen($prevLine) < 80) {
                        $title = trim(str_replace(':', '', $prevLine));
                    }
                }

                // Fallback Title
                if (empty($title)) {
                    $title = "مقطع $timeString";
                }

                // SAVE PREVIOUS SEGMENT
                if ($currentSegment) {
                    $parsed[] = $currentSegment;
                }

                // START NEW SEGMENT
                $currentSegment = [
                    'title' => $title,
                    'start' => $seconds,
                    'content' => ''
                ];

                // If there was text in the same line after removing time, add it to content?
                // Usually not, usually it's just "Speaker: (Time)"

            } else {
                // CONTENT LINE
                if ($currentSegment) {
                    $currentSegment['content'] .= $line . "\n";
                }
            }
        }

        // Add last segment
        if ($currentSegment) {
            $parsed[] = $currentSegment;
        }

        return $parsed;
    }

    protected function storeSegments($media, $segments)
    {
        $type = ($media instanceof Audio) ? 'audio' : 'video';
        $nodeType = ($type === 'audio') ? 'segment' : 'scene';

        $startOrder = $this->contentService->getMaxOrder($media) + 1;

        foreach ($segments as $index => $seg) {
            $nextSeg = $segments[$index + 1] ?? null;
            $endTime = $nextSeg ? $nextSeg['start'] : 0; // 0 means until end/unknown

            // Create Node
            $this->contentService->createNode($media, [
                'type' => $nodeType,
                'title' => $seg['title'],
                'slug' => Str::slug($seg['title']) . '-' . Str::random(6),
                'content' => nl2br(trim($seg['content'])),
                'start_time' => $seg['start'],
                'end_time' => $endTime, // Calculate duration based on next segment
                'order' => $startOrder + $index
            ]);

            $this->line("      + Created: [{$this->secondsToTime($seg['start'])}] {$seg['title']}");
        }
    }

    protected function timeToSeconds($str)
    {
        if (!$str)
            return 0;
        $parts = array_reverse(explode(':', $str));
        $seconds = 0;
        $multiplier = 1;
        foreach ($parts as $part) {
            $seconds += (int) $part * $multiplier;
            $multiplier *= 60;
        }
        return $seconds;
    }

    protected function secondsToTime($seconds)
    {
        return gmdate("H:i:s", $seconds);
    }
}
