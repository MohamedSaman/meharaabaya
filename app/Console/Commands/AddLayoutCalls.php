<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class AddLayoutCalls extends Command
{
    protected $signature = 'livewire:add-layout-calls';
    protected $description = 'Add ->layout() calls to render methods in Livewire components';

    public function handle()
    {
        $this->info('Adding ->layout() calls to render methods...');
        
        $path = app_path('Livewire/Admin');
        $files = File::allFiles($path);
        
        $updated = 0;
        $skipped = 0;
        
        foreach ($files as $file) {
            $content = File::get($file->getPathname());
            
            // Check if it has WithDynamicLayout trait
            if (!str_contains($content, 'use WithDynamicLayout')) {
                $this->warn("Skipping {$file->getFilename()} - doesn't use WithDynamicLayout trait");
                $skipped++;
                continue;
            }
            
            // Check if render method already has ->layout()
            if (preg_match("/->layout\(/", $content)) {
                $this->warn("Skipping {$file->getFilename()} - already has ->layout() call");
                $skipped++;
                continue;
            }
            
            // Check if render method exists
            if (preg_match("/public function render\(\)/", $content)) {
                $this->info("Updating {$file->getFilename()}...");
                
                // Add ->layout($this->layout) after return view(...);
                // This regex matches: return view('...', [...]);  or  return view('...');
                $content = preg_replace(
                    "/(return view\([^;]+\))(;)/",
                    "$1->layout(\$this->layout)$2",
                    $content
                );
                
                // Save the file
                File::put($file->getPathname(), $content);
                
                $this->line("✓ Updated {$file->getFilename()}");
                $updated++;
            } else {
                $this->warn("Skipping {$file->getFilename()} - no render method found");
                $skipped++;
            }
        }
        
        $this->newLine();
        $this->info("Summary:");
        $this->line("✓ Updated: {$updated} files");
        $this->line("→ Skipped: {$skipped} files");
        $this->newLine();
        $this->info('Done! All render methods have been updated.');
        
        return Command::SUCCESS;
    }
}
