<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class AnalyzeArchitecture extends Command
{
    protected $signature = 'analyze:architecture 
                            {directories?* : Directories to analyze}
                            {--no-recursive : Don\'t scan subdirectories}
                            {--output= : Output file path}';

    protected $description = 'Analyze project architecture and detect code duplication';

    protected $stats = [
        'total_files' => 0,
        'total_lines' => 0,
        'total_functions' => 0,
    ];

    protected $fileContent = [];

    public function handle()
    {
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('       SMART PROJECT ARCHITECTURE ANALYSIS');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->newLine();

        $directories = $this->argument('directories');
        
        if (empty($directories)) {
            $directories = $this->selectDirectoriesInteractively();
        }

        $recursive = !$this->option('no-recursive');
        $outputFile = $this->option('output') ?: 'architecture_analysis_' . date('Ymd_His') . '.md';

        $this->info("📁 Working Directory: " . base_path());
        $this->info("📅 Analysis Date: " . now()->format('Y-m-d H:i:s'));
        $this->info("🔍 Scanning Directories: " . implode(', ', $directories));
        $this->info("♻️  Recursive: " . ($recursive ? 'Yes' : 'No'));
        $this->info("📄 Output File: " . $outputFile);
        $this->newLine();

        // Initialize file content in Markdown format
        $this->addToFile("# 📊 Project Architecture Analysis");
        $this->addToFile("");
        $this->addToFile("**Generated:** " . now()->format('Y-m-d H:i:s'));
        $this->addToFile("");
        $this->addToFile("**Working Directory:** `" . base_path() . "`");
        $this->addToFile("");
        $this->addToFile("**Scanned Directories:**");
        foreach ($directories as $dir) {
            $this->addToFile("- `$dir`");
        }
        $this->addToFile("");
        $this->addToFile("**Recursive Scan:** " . ($recursive ? 'Yes' : 'No'));
        $this->addToFile("");
        $this->addToFile("---");

        // Analysis sections
        $this->displayHeader('PROJECT STATISTICS');
        $this->displayStatistics($directories, $recursive);

        foreach ($directories as $directory) {
            if (!File::isDirectory(base_path($directory))) {
                $this->warn("⚠️  Directory not found: $directory");
                continue;
            }

            $this->displayHeader("ANALYZING: $directory");
            $this->analyzeDirectory($directory, $recursive);
        }

        $this->displayHeader('DUPLICATION ANALYSIS');
        $this->analyzeDuplication($directories);

        $this->displayHeader('LARGEST FILES (TOP 10)');
        $this->displayLargestFiles($directories);

        $this->displayHeader('RECOMMENDATIONS');
        $this->displayRecommendations($directories);

        // Save to file
        File::put(base_path($outputFile), implode("\n", $this->fileContent));

        $this->newLine();
        $this->info('✅ Analysis completed successfully!');
        $this->info("📄 Report saved to: " . base_path($outputFile));
        $this->newLine();

        return Command::SUCCESS;
    }

    protected function addToFile($line)
    {
        $this->fileContent[] = $line;
    }

    protected function selectDirectoriesInteractively()
    {
        $this->info('📂 Available directories for analysis:');
        $this->newLine();

        $availableDirs = [
            'app/Http/Controllers', 'app/Http/Middleware', 'app/Models', 'app/Services',
            'app/Traits', 'app/Helpers', 'app/Providers', 'app/Events', 'app/Listeners',
            'app/Jobs', 'app/Mail', 'app/Notifications', 'app/Policies', 'app/Rules',
            'resources/js/Pages', 'resources/js/Components', 'resources/js/Layouts',
            'tests/Feature', 'tests/Unit',
        ];

        $existingDirs = [];
        foreach ($availableDirs as $dir) {
            if (File::isDirectory(base_path($dir))) {
                $existingDirs[] = $dir;
            }
        }

        foreach ($existingDirs as $index => $dir) {
            $fileCount = count($this->getPhpFiles(base_path($dir), true));
            $this->line(sprintf('  [%d] %s (%d files)', $index + 1, $dir, $fileCount));
        }

        $this->newLine();
        $this->info('💡 Enter directory numbers separated by commas (e.g., 1,3,5)');
        $this->info('💡 Or press Enter to analyze all directories');
        $this->info('💡 Or type "0" to cancel');
        $this->newLine();

        $input = $this->ask('Select directories');

        if (empty($input)) {
            $this->info('✓ Analyzing all available directories');
            return $existingDirs;
        }

        if ($input === '0') {
            $this->warn('Analysis cancelled');
            exit(0);
        }

        $selectedNumbers = array_map('trim', explode(',', $input));
        $selectedDirs = [];

        foreach ($selectedNumbers as $num) {
            if (!is_numeric($num)) {
                $this->error("Invalid input: $num");
                continue;
            }

            $index = (int)$num - 1;
            if (isset($existingDirs[$index])) {
                $selectedDirs[] = $existingDirs[$index];
            } else {
                $this->warn("Invalid number: $num");
            }
        }

        if (empty($selectedDirs)) {
            $this->error('No valid directories selected. Using defaults.');
            return ['app/Http/Controllers', 'app/Models', 'app/Services'];
        }

        $this->newLine();
        $this->info('✓ Selected directories:');
        foreach ($selectedDirs as $dir) {
            $this->line("  • $dir");
        }
        $this->newLine();

        return $selectedDirs;
    }

    protected function displayHeader($title)
    {
        $this->newLine();
        $this->info("═══ $title ═══");
        $this->newLine();
        
        $this->addToFile("");
        $this->addToFile("## " . $title);
        $this->addToFile("");
    }

    protected function displayStatistics($directories, $recursive)
    {
        // Add table header
        $this->addToFile("| Directory | PHP Files |");
        $this->addToFile("|-----------|-----------|");
        
        foreach ($directories as $directory) {
            $path = base_path($directory);
            if (!File::isDirectory($path)) continue;

            $files = $this->getPhpFiles($path, $recursive);
            $count = count($files);
            
            $line = "📊 $directory: $count PHP files";
            $this->info($line);
            $this->addToFile("| `$directory` | $count |");
            
            $this->stats['total_files'] += $count;
        }

        $this->newLine();
        $line = "📊 Total PHP files: {$this->stats['total_files']}";
        $this->info($line);
        $this->addToFile("");
        $this->addToFile("**Total PHP Files:** {$this->stats['total_files']}");
    }

    protected function analyzeDirectory($directory, $recursive)
    {
        $path = base_path($directory);
        $files = $this->getPhpFiles($path, $recursive);

        $bar = $this->output->createProgressBar(count($files));
        $bar->start();

        foreach ($files as $file) {
            $this->analyzeFile($file->getRealPath());
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
    }

    protected function analyzeFile($filePath)
    {
        $content = File::get($filePath);
        $lines = substr_count($content, "\n") + 1;
        
        preg_match_all('/public function\s+(\w+)/', $content, $publicMatches);
        preg_match_all('/protected function\s+(\w+)/', $content, $protectedMatches);
        preg_match_all('/private function\s+(\w+)/', $content, $privateMatches);
        
        // Detect Pest test syntax
        preg_match_all('/^(test|it)\s*\(/', $content, $pestMatches, 2); // PREG_MULTILINE = 2
        
        $publicCount = count($publicMatches[0] ?? []);
        $protectedCount = count($protectedMatches[0] ?? []);
        $privateCount = count($privateMatches[0] ?? []);
        $pestCount = count($pestMatches[0] ?? []);
        
        $relativePath = str_replace(base_path() . '/', '', $filePath);
        
        $this->addToFile("");
        $this->addToFile("### 📄 `$relativePath`");
        $this->addToFile("");
        $this->addToFile("- **Lines:** $lines");
        
        if ($pestCount > 0) {
            $this->addToFile("- **Pest Tests:** $pestCount");
        }
        
        $this->addToFile("- **Functions:** $publicCount public, $protectedCount protected, $privateCount private");
        $this->addToFile("");
        
        if ($publicCount > 0) {
            $this->addToFile("**Public Functions:**");
            foreach ($publicMatches[0] as $func) {
                $this->addToFile("- `$func`");
            }
            $this->addToFile("");
        }
        
        if ($pestCount > 0) {
            $this->addToFile("**Pest Tests:**");
            foreach ($pestMatches[0] as $test) {
                $this->addToFile("- `$test`");
            }
            $this->addToFile("");
        }
        
        $this->stats['total_lines'] += $lines;
        $this->stats['total_functions'] += $publicCount + $protectedCount + $privateCount + $pestCount;
    }

    protected function analyzeDuplication($directories)
    {
        $line = '🔍 CRUD Functions Distribution:';
        $this->info($line);
        $this->addToFile("");
        $this->newLine();

        $crudMethods = ['index', 'show', 'store', 'update', 'destroy'];
        
        foreach ($directories as $directory) {
            $path = base_path($directory);
            if (!File::isDirectory($path)) continue;

            $dirLine = "**Directory:** `$directory`";
            $this->info("  Directory: $directory");
            $this->addToFile("");
            $this->addToFile($dirLine);
            $this->addToFile("");
            $this->addToFile("| Method | Count |");
            $this->addToFile("|--------|-------|");
            
            foreach ($crudMethods as $method) {
                $count = 0;
                $files = $this->getPhpFiles($path, true);
                
                foreach ($files as $file) {
                    $content = File::get($file->getRealPath());
                    if (preg_match("/public function $method\s*\(/", $content)) {
                        $count++;
                    }
                }
                
                $methodLine = "    $method() → $count files";
                $this->line($methodLine);
                $this->addToFile("| `$method()` | $count |");
            }
            
            $this->newLine();
        }

        $suggestion = '💡 **Suggestion:** Consider using a Base Controller or Trait for repeated CRUD logic';
        $this->info($suggestion);
        $this->addToFile("");
        $this->addToFile("> 💡 **Suggestion:** Consider using a Base Controller or Trait for repeated CRUD logic");
    }

    protected function displayLargestFiles($directories)
    {
        $allFiles = [];
        
        foreach ($directories as $directory) {
            $path = base_path($directory);
            if (!File::isDirectory($path)) continue;
            
            $files = $this->getPhpFiles($path, true);
            foreach ($files as $file) {
                $filePath = $file->getRealPath();
                $lines = substr_count(File::get($filePath), "\n") + 1;
                $allFiles[$filePath] = $lines;
            }
        }
        
        arsort($allFiles);
        $top10 = array_slice($allFiles, 0, 10, true);
        
        $this->addToFile("| Lines | File |");
        $this->addToFile("|-------|------|");
        
        foreach ($top10 as $file => $lines) {
            $relativePath = str_replace(base_path() . '/', '', $file);
            $line = "   📏 $lines lines → $relativePath";
            $this->line($line);
            $this->addToFile("| $lines | `$relativePath` |");
        }
    }

    protected function displayRecommendations($directories)
    {
        $largeFiles = 0;
        foreach ($directories as $directory) {
            $path = base_path($directory);
            if (!File::isDirectory($path)) continue;
            
            $files = $this->getPhpFiles($path, true);
            foreach ($files as $file) {
                $lines = substr_count(File::get($file->getRealPath()), "\n") + 1;
                if ($lines > 300) {
                    $largeFiles++;
                }
            }
        }
        
        if ($largeFiles > 0) {
            $warning = "⚠️  Found $largeFiles files with >300 lines";
            $suggestion = "   → Consider breaking them into smaller classes";
            $this->warn($warning);
            $this->line($suggestion);
            $this->addToFile("");
            $this->addToFile("> ⚠️ **Warning:** Found $largeFiles files with >300 lines");
            $this->addToFile("> ");
            $this->addToFile("> Consider breaking them into smaller classes");
            $this->newLine();
        }
        
        $this->info("📊 Summary:");
        $this->info("   Total Files: {$this->stats['total_files']}");
        $this->info("   Total Lines: {$this->stats['total_lines']}");
        $this->info("   Total Functions: {$this->stats['total_functions']}");
        
        $this->addToFile("");
        $this->addToFile("### 📊 Summary");
        $this->addToFile("");
        $this->addToFile("| Metric | Count |");
        $this->addToFile("|--------|-------|");
        $this->addToFile("| Total Files | {$this->stats['total_files']} |");
        $this->addToFile("| Total Lines | {$this->stats['total_lines']} |");
        $this->addToFile("| Total Functions | {$this->stats['total_functions']} |");
    }

    protected function getPhpFiles($directory, $recursive = true)
    {
        if ($recursive) {
            return File::allFiles($directory);
        }
        
        return collect(File::files($directory))
            ->filter(fn($file) => $file->getExtension() === 'php')
            ->all();
    }
}
