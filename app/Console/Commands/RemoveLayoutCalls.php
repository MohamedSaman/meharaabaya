<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class RemoveLayoutCalls extends Command
{
    protected $signature = 'livewire:remove-layout-calls';
    protected $description = 'Remove ->layout() calls from render methods (trait handles it now)';

    public function handle()
    {
        $this->info('Removing ->layout() calls from render methods...');
        
        $path = app_path('Livewire/Admin');
        $files = File::allFiles($path);
        
        $updated = 0;
        $skipped = 0;
        
        foreach ($files as $file) {
            $content = File::get($file->getPathname());
            
            // Check if it has ->layout() call in render method
            if (preg_match("/->layout\(\\\$this->layout\)/", $content)) {
                $this->info("Updating {$file->getFilename()}...");
                
                // Remove ->layout($this->layout) from return statements
                $content = preg_replace(
                    "/->layout\(\\\$this->layout\)/",
                    "",
                    $content
                );
                
                // Save the file
                File::put($file->getPathname(), $content);
                
                $this->line("✓ Updated {$file->getFilename()}");
                $updated++;
            } else {
                $skipped++;
            }
        }
        
        $this->newLine();
        $this->info("Summary:");
        $this->line("✓ Updated: {$updated} files");
        $this->line("→ Skipped: {$skipped} files");
        $this->newLine();
        $this->info('Done! All ->layout() calls have been removed.');
        
        return Command::SUCCESS;
    }
}
