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

    protected $contentService;

    public function __construct(\App\Services\EntityContentService $contentService)
    {
        parent::__construct();
        $this->contentService = $contentService;
    }

    /**
     * Mapping of directories to models and their allowed extensions.
     */
    protected $config = [
        'books' => [
            'model' => Book::class,
            'extensions' => ['pdf', 'epub', 'mobi', 'docx', 'md', 'txt', 'odt'],
        ],
        'audio' => [
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

        // Wipe existing content collections to ensure a fresh sync
        \App\Models\BookChild::truncate();
        \App\Models\ManuscriptPage::truncate();
        \App\Models\AudioSegment::truncate();
        \App\Models\VideoSegment::truncate();
        $this->info('Cleared existing content collections.');

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

        // 1. Special handling for Manuscripts (Folder Bundles)
        if ($modelClass === Manuscript::class) {
            $this->syncManuscriptBundles($dir, $extensions);
        }

        // 2. Discover files recursively
        $files = Storage::disk('public')->allFiles($dir);
        $count = 0;

        foreach ($files as $filePath) {
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

            if (!in_array($extension, $extensions)) {
                $this->warn("Skipping file (ext: $extension): $filePath");
                continue;
            }

            // For manuscripts, if it's inside a subdirectory, skip it here if it's an image (already handled as bundle)
            if ($modelClass === Manuscript::class && dirname($filePath) !== $dir) {
                if (in_array($extension, ['jpg', 'png', 'jpeg', 'tiff']))
                    continue;
            }
            $this->info("Processing file: $filePath");

            $fileName = pathinfo($filePath, PATHINFO_FILENAME);
            $title = Str::headline($fileName);
            $slug = Str::slug($title);

            // 1. Find or Create the abstract Entity (Book, Audio, Video, Manuscript)
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

            // 2. Approach A: Extract Tags from parent folders
            $relativePath = str_replace($dir . DIRECTORY_SEPARATOR, '', $filePath);
            $parts = explode(DIRECTORY_SEPARATOR, $relativePath);
            array_pop($parts); // remove filename

            foreach ($parts as $part) {
                $tagName = Str::headline($part);
                $this->syncEntityTags($entity, $tagName);
            }

            // 2. Check if this VERSION already exists for this file
            $versionExists = \App\Models\Version::where('file_path', $filePath)->exists();

            if (!$versionExists || $this->option('force')) {
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

                $this->line("  [+] Synced Version: {$title}");
            }

            $count++;

            // 4. Create separate content nodes (Always run to ensure fresh content after truncate)
            // Book Content
            if ($modelClass === Book::class && in_array($extension, ['md', 'txt', 'docx'])) {
                $this->syncBookContentFromHeaders($entity, $filePath, $extension);
            }
            // Audio Content
            elseif ($modelClass === Audio::class) {
                // Ensure we don't duplicate if run multiple times without truncate, but handle() truncates so it's safe.
                $this->contentService->createNode($entity, [
                    'type' => 'segment',
                    'title' => 'Default Segment',
                    'slug' => 'seg-1-' . substr($slug, 0, 4),
                    'content' => [],
                    'order' => 1
                ]);
            }
            // Video Content
            elseif ($modelClass === Video::class) {
                $this->contentService->createNode($entity, [
                    'type' => 'scene',
                    'title' => 'Default Scene',
                    'slug' => 'scn-1-' . substr($slug, 0, 4),
                    'content' => [],
                    'order' => 1
                ]);
            }
            // Manuscript Content
            elseif ($modelClass === Manuscript::class) {
                $this->contentService->createNode($entity, [
                    'type' => 'page',
                    'title' => 'Default Page',
                    'slug' => 'page-1-' . substr($slug, 0, 4),
                    'content' => '<p>Default Manuscript Content (Image Placeholder)</p>',
                    'image_url' => asset('storage/' . $dir . '/' . $fileName . '.' . $extension),
                    'order' => 1
                ]);
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

        // Protect manually edited chapters
        \App\Models\BookChild::where('book_id', $book->id)
            ->where('is_manually_edited', '!=', true)
            ->delete();

        $content = Storage::disk('public')->get($filePath);
        $nodes = [];

        if ($extension === 'md' || $extension === 'txt') {
            $nodes = $this->parseMarkdownHeaders($content);
        } elseif ($extension === 'docx') {
            $nodes = $this->parseDocxHeaders($filePath);
        }

        if (empty($nodes)) {
            $this->warn("      No headers found. Creating a default node with extractable text.");

            $finalText = '';
            if ($extension === 'docx') {
                $finalText = $this->getDocxPlainText($filePath);
            } else {
                $finalText = $content;
            }

            // Create a default node
            $service->addChild($book, [
                'parent_id' => null,
                'type' => 'chapter',
                'title' => 'Default Content',
                'order' => 1,
                'content' => "<p>" . nl2br(htmlspecialchars($finalText)) . "</p>",
            ]);
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
                3 => 'bab',
                4 => 'chapter',
                5 => 'masala'
            ];
            $type = $typeMap[$level] ?? 'chapter';

            // Check if a manually edited version already exists
            $existingProtected = \App\Models\BookChild::where('book_id', $book->id)
                ->where('title', $nodeData['title'])
                ->where('is_manually_edited', true)
                ->first();

            if ($existingProtected) {
                $this->warn("      [!] Skipping manually edited chapter: {$nodeData['title']}");
                $parentsStack[$level] = $existingProtected->id;
                continue;
            }

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

    private function getDocxPlainText(string $filePath): string
    {
        $fullPath = Storage::disk('public')->path($filePath);
        $zip = new \ZipArchive();
        $text = '';

        if ($zip->open($fullPath) === true) {
            $xmlString = $zip->getFromName('word/document.xml');
            $zip->close();
            if ($xmlString) {
                $xml = new \SimpleXMLElement($xmlString);
                $xml->registerXPathNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
                $paragraphs = $xml->xpath('//w:p');
                foreach ($paragraphs as $p) {
                    foreach ($p->xpath('.//w:t') as $t) {
                        $text .= (string) $t . " ";
                    }
                    $text .= "\n";
                }
            }
        }
        return trim($text);
    }

    protected function syncEntityTags($entity, string $tagName)
    {
        $tag = \App\Models\Tag::firstOrCreate(
            ['name' => $tagName],
            ['slug' => Str::slug($tagName), 'type' => 'category']
        );

        if (!$entity->tags->contains($tag->id)) {
            $entity->tags()->attach($tag->id);
            $this->line("    [#] Tagged with: {$tagName}");
        }
    }

    protected function syncManuscriptBundles(string $dir, array $extensions)
    {
        $directories = Storage::disk('public')->directories($dir);

        foreach ($directories as $subDir) {
            $files = Storage::disk('public')->files($subDir);
            $imageFiles = array_filter($files, function ($file) {
                return in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'png', 'jpeg', 'tiff']);
            });

            if (empty($imageFiles))
                continue;

            $this->info("Processing Manuscript Bundle: {$subDir}");

            $folderName = basename($subDir);
            $title = Str::headline($folderName);
            $slug = Str::slug($title);

            // 1. Create Manuscript Entity
            $entity = Manuscript::where('slug', $slug)->first();
            if (!$entity) {
                $entity = Manuscript::create([
                    'slug' => $slug,
                    'title' => $title,
                    'description' => 'Image bundle synced from ' . $subDir,
                    'file_path' => $subDir,
                ]);
            }

            // 2. Sync Images as Pages
            $order = 1;
            foreach ($imageFiles as $img) {
                $imgName = pathinfo($img, PATHINFO_FILENAME);
                $this->contentService->createNode($entity, [
                    'type' => 'page',
                    'title' => Str::headline($imgName),
                    'slug' => Str::slug($imgName) . '-' . $entity->id,
                    'content' => '<p>Manuscript Page from Bundle</p>',
                    'image_url' => asset('storage/' . $img),
                    'order' => $order++
                ]);
            }
            $this->line("  [+] Bundle Synced: {$title} (" . count($imageFiles) . " pages)");
        }
    }
}
