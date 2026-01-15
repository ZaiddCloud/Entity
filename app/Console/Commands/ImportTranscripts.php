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

        // Regex for time: (04:23) or (1:04:23)
        // Matches pattern: Text(04:23) or just (04:23)
        $timePattern = '/(.*?)\(?(\d{1,2}:\d{2}(?::\d{2})?)\)?/';

        foreach ($lines as $i => $line) {
            $line = trim($line);
            if (empty($line))
                continue;

            // Check if line contains timestamp at the end or alone
            // Looking for the specific format user mentioned: "Name(04:23)"
            if (preg_match($timePattern, $line, $matches)) {
                $titleCandidate = trim($matches[1]);
                $timeString = $matches[2];
                $seconds = $this->timeToSeconds($timeString);

                // If title is empty, maybe check previous line?
                // User said: "Title in a line, followed by line with Title(Time)" or just "Title(Time)"
                $title = $titleCandidate;

                // If the regex captured a title empty or very short, and previous line exists
                if (mb_strlen($title) < 2 && isset($lines[$i - 1])) {
                    $prevLine = trim($lines[$i - 1]);
                    // Check if previous line looks like a title (ends with :)
                    if (Str::endsWith($prevLine, ':') || mb_strlen($prevLine) < 50) {
                        $title = $prevLine;
                    }
                }

                // Cleanup title (remove :)
                $title = trim(str_replace(':', '', $title));
                if (empty($title))
                    $title = "مقطع $timeString";

                // Save previous segment content
                if ($currentSegment) {
                    $parsed[] = $currentSegment;
                }

                // Start new segment
                $currentSegment = [
                    'title' => $title,
                    'start' => $seconds,
                    'content' => ''
                ];
            } else {
                // Determine if this is content or just a title line waiting for next time line
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
