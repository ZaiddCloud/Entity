<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Book;
use App\Models\Audio;
use App\Models\Video;
use App\Models\Manuscript;

class StorageSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:sync {--force : Overwrite existing records}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan storage directories and sync files with the database';

    /**
     * Mapping of directories to models and their allowed extensions.
     */
    protected $config = [
        'books' => [
            'model' => Book::class,
            'extensions' => ['pdf', 'epub', 'mobi', 'docx', 'md', 'txt', 'odt'],
        ],
        'audios' => [
            'model' => Audio::class,
            'extensions' => ['mp3', 'wav', 'm4a', 'aac'],
        ],
        'videos' => [
            'model' => Video::class,
            'extensions' => ['mp4', 'mkv', 'avi', 'mov'],
        ],
        'manuscripts' => [
            'model' => Manuscript::class,
            'extensions' => ['pdf', 'jpg', 'png', 'jpeg', 'tiff'],
        ],
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting storage synchronization...');

        foreach ($this->config as $dir => $settings) {
            $this->syncDirectory($dir, $settings['model'], $settings['extensions']);
        }

        $this->info('Synchronization completed successfully!');
    }

    /**
     * Sync a specific directory with its corresponding model.
     */
    protected function syncDirectory(string $dir, string $modelClass, array $extensions)
    {
        $this->comment("Scanning directory: storage/app/public/{$dir}");

        if (!Storage::disk('public')->exists($dir)) {
            Storage::disk('public')->makeDirectory($dir);
            $this->line("Created missing directory: {$dir}");
            return;
        }

        $files = Storage::disk('public')->files($dir);
        $count = 0;

        foreach ($files as $filePath) {
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

            if (!in_array($extension, $extensions)) {
                continue;
            }

            $fileName = pathinfo($filePath, PATHINFO_FILENAME);
            $title = Str::headline($fileName);
            $slug = Str::slug($title);

            $fileName = pathinfo($filePath, PATHINFO_FILENAME);
            $title = Str::headline($fileName);
            $slug = Str::slug($title);

            // 1. Check if this VERSION already exists for this file
            $versionExists = \App\Models\Version::where('file_path', $filePath)->exists();

            if (!$versionExists || $this->option('force')) {
                // 2. Find or Create the abstract Entity (Book, Audio, Video, Manuscript)
                $entity = $modelClass::where('slug', $slug)->first();

                if (!$entity) {
                    $entity = $modelClass::create([
                        'slug' => $slug,
                        'title' => $title,
                        'description' => 'Automatically synced from storage.',
                        'file_path' => $filePath,
                    ]);
                } elseif ($this->option('force')) {
                    $entity->update([
                        'title' => $title,
                        'description' => 'Automatically synced from storage.',
                        'file_path' => $filePath,
                    ]);
                }

                // 3. Create/Update the Version (Polymorphic)
                \App\Models\Version::updateOrCreate(
                    ['file_path' => $filePath],
                    [
                        'versionable_id' => $entity->id,
                        'versionable_type' => $entity->type, // This uses the model's type attribute (audios, videos, etc.)
                        'format' => $extension,
                        'file_size' => Storage::disk('public')->size($filePath),
                        'edition_number' => 1,
                    ]
                );

                $count++;
                $this->line("  [+] Synced: {$title}");

                // 4. If it's a Book and the file is a source format, sync its content hierarchy
                if ($modelClass === Book::class && in_array($extension, ['md', 'txt', 'docx'])) {
                    $this->syncBookContentFromHeaders($entity, $filePath, $extension);
                }
            }
        }

        $this->info("Synced {$count} items for {$dir}.");
    }

    /**
     * Sync book content by parsing headers within the file.
     */
    protected function syncBookContentFromHeaders(Book $book, string $filePath, string $extension)
    {
        $this->comment("    Parsing headers for: {$book->title} ({$extension})");

        $service = new \App\Services\BookContentService();
        \App\Models\BookChild::where('book_id', $book->id)->delete();

        $content = Storage::disk('public')->get($filePath);
        $nodes = [];

        if ($extension === 'md' || $extension === 'txt') {
            $nodes = $this->parseMarkdownHeaders($content);
        } elseif ($extension === 'docx') {
            $nodes = $this->parseDocxHeaders($filePath);
        }

        if (empty($nodes)) {
            $this->warn("      No headers found in the file structure.");
            return;
        }

        $this->buildHierarchy($service, $book, $nodes);
    }

    private function parseMarkdownHeaders(string $content): array
    {
        // 1. Extract Footnote Definitions
        $lineList = explode("\n", $content);
        $footnotes = [];
        $cleanLines = [];

        foreach ($lineList as $line) {
            if (preg_match('/^\[\^(\d+)\]:\s*(.+)$/', trim($line), $matches)) {
                $footnotes[$matches[1]] = trim($matches[2]);
            } else {
                $cleanLines[] = $line;
            }
        }
        $content = implode("\n", $cleanLines);

        // 2. Parse Headers
        $lines = explode("\n", $content);
        $nodes = [];
        $currentText = [];

        foreach ($lines as $line) {
            if (preg_match('/^(#{1,6})\s+(.+)$/', trim($line), $matches)) {
                if (!empty($nodes)) {
                    $nodes[count($nodes) - 1]['text'] = implode("\n", $currentText);
                    $currentText = [];
                }

                $nodes[] = [
                    'level' => strlen($matches[1]),
                    'title' => $matches[2],
                    'text' => ''
                ];
            } else {
                $currentText[] = $line;
            }
        }

        if (!empty($nodes)) {
            $nodes[count($nodes) - 1]['text'] = implode("\n", $currentText);
        }

        // 3. Map Footnotes to Nodes (simple auto-mapping for now)
        foreach ($nodes as &$node) {
            $node['annotations'] = [];
            if (preg_match_all('/\[\^(\d+)\]/', $node['text'], $matches)) {
                foreach ($matches[1] as $id) {
                    if (isset($footnotes[$id])) {
                        $node['annotations'][] = [
                            'type' => 'footnote',
                            'marker' => "[$id]",
                            'content' => $footnotes[$id]
                        ];
                    }
                }
            }
        }

        return $nodes;
    }

    private function parseDocxHeaders(string $filePath): array
    {
        $fullPath = Storage::disk('public')->path($filePath);
        $zip = new \ZipArchive();
        $nodes = [];
        $docxFootnotes = [];

        if ($zip->open($fullPath) === true) {
            // A. Extract Footnotes mapping
            $footnoteXml = $zip->getFromName('word/footnotes.xml');
            if ($footnoteXml) {
                $fXml = new \SimpleXMLElement($footnoteXml);
                $fXml->registerXPathNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
                foreach ($fXml->xpath('//w:footnote') as $fn) {
                    $attr = $fn->attributes('http://schemas.openxmlformats.org/wordprocessingml/2006/main');
                    $id = (string) ($attr['id'] ?? '');
                    $text = strip_tags($fn->asXML());
                    if (!empty($id) && !empty(trim($text))) {
                        $docxFootnotes[$id] = trim($text);
                    }
                }
            }

            // B. Process Document
            $xmlString = $zip->getFromName('word/document.xml');
            $zip->close();
            if (!$xmlString)
                return [];

            $xml = new \SimpleXMLElement($xmlString);
            $xml->registerXPathNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

            $paragraphs = $xml->xpath('//w:p');
            $currentText = [];
            $currentAnnotations = [];

            foreach ($paragraphs as $p) {
                // Style/Header check
                $isHeader = false;
                $level = 0;
                $style = $p->xpath('.//w:pPr/w:pStyle/@w:val');
                if (!empty($style)) {
                    $styleName = (string) $style[0];
                    if (preg_match('/Heading(\d+)/i', $styleName, $matches)) {
                        $isHeader = true;
                        $level = (int) $matches[1];
                    }
                }

                // Text and Footnote references
                $pText = '';
                foreach ($p->xpath('.//w:r') as $r) {
                    // Check for text
                    foreach ($r->xpath('.//w:t') as $t) {
                        $pText .= (string) $t;
                    }
                    // Check for footnote reference
                    $fnRefs = $r->xpath('.//w:footnoteReference');
                    foreach ($fnRefs as $ref) {
                        $attr = $ref->attributes('http://schemas.openxmlformats.org/wordprocessingml/2006/main');
                        $id = (string) ($attr['id'] ?? '');

                        if (!empty($id) && isset($docxFootnotes[$id])) {
                            $marker = "[" . (count($currentAnnotations) + 1) . "]";
                            $pText .= $marker;
                            $currentAnnotations[] = [
                                'type' => 'footnote',
                                'marker' => $marker,
                                'content' => $docxFootnotes[$id]
                            ];
                        }
                    }
                }

                if ($isHeader && !empty(trim($pText))) {
                    if (!empty($nodes)) {
                        $nodes[count($nodes) - 1]['text'] = implode("\n\n", $currentText);
                        $nodes[count($nodes) - 1]['annotations'] = $currentAnnotations;
                        $currentText = [];
                        $currentAnnotations = [];
                    }
                    $nodes[] = [
                        'level' => $level,
                        'title' => trim($pText),
                        'text' => '',
                        'annotations' => []
                    ];
                } else {
                    if (!empty(trim($pText))) {
                        $currentText[] = $pText;
                    }
                }
            }

            if (!empty($nodes)) {
                $nodes[count($nodes) - 1]['text'] = implode("\n\n", $currentText);
                $nodes[count($nodes) - 1]['annotations'] = $currentAnnotations;
            }
        }
        return $nodes;
    }

    private function buildHierarchy($service, $book, array $nodes)
    {
        $parentsStack = []; // Initializing stack for hierarchy tracking
        $order = 1;

        foreach ($nodes as $nodeData) {
            $level = $nodeData['level'];

            // Find parent: The closest level above the current level in the stack
            $parentId = null;
            for ($l = $level - 1; $l >= 1; $l--) {
                if (isset($parentsStack[$l])) {
                    $parentId = $parentsStack[$l];
                    break;
                }
            }

            // Map level to type name for aesthetics
            $typeMap = [
                1 => 'sub-book',
                2 => 'part',
                3 => 'door',
                4 => 'chapter',
                5 => 'masala'
            ];
            $type = $typeMap[$level] ?? 'chapter';

            $node = $service->addChild($book, [
                'parent_id' => $parentId,
                'type' => $type,
                'title' => $nodeData['title'],
                'order' => $order++
            ]);

            // Save this node as potential parent for next items
            $parentsStack[$level] = $node->id;

            // Clear any deeper levels from stack as we have moved back up or stayed at same depth
            foreach ($parentsStack as $l => $id) {
                if ($l > $level)
                    unset($parentsStack[$l]);
            }

            // Add text content if present
            if (!empty(trim($nodeData['text']))) {
                $service->addBlock($node, [
                    'type' => 'paragraph',
                    'body' => trim($nodeData['text']),
                    'annotations' => $nodeData['annotations'] ?? []
                ]);
            }
        }

        $this->info("      Successfully built hierarchy with " . count($nodes) . " nodes.");
    }
}
