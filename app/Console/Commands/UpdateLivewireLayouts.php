<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class UpdateLivewireLayouts extends Command
{
    protected $signature = 'livewire:update-layouts';
    protected $description = 'Update all Admin Livewire components to use dynamic layouts';

    public function handle()
    {
        $this->info('Updating Livewire components to use dynamic layouts...');
        
        $path = app_path('Livewire/Admin');
        $files = File::allFiles($path);
        
        $updated = 0;
        $skipped = 0;
        
        foreach ($files as $file) {
            $content = File::get($file->getPathname());
            
            // Skip if already has WithDynamicLayout
            if (str_contains($content, 'use WithDynamicLayout')) {
                $this->warn("Skipping {$file->getFilename()} - already updated");
                $skipped++;
                continue;
            }
            
            // Check if it has the Layout attribute
            if (preg_match("/#\[Layout\('components\.layouts\.admin'\)\]/", $content)) {
                $this->info("Updating {$file->getFilename()}...");
                
                // Remove the #[Layout('components.layouts.admin')] line
                $content = preg_replace("/#\[Layout\('components\.layouts\.admin'\)\]\n/", "", $content);
                
                // Add the trait import after the last 'use' statement before the class
                $content = preg_replace(
                    "/(use [^;]+;)(\s*)(#\[)/",
                    "$1\nuse App\\Livewire\\Concerns\\WithDynamicLayout;\n\n$3",
                    $content,
                    1
                );
                
                // Add the trait usage in the class (after "class ClassName extends Component {")
                $content = preg_replace(
                    "/(class \w+ extends Component\s*\{)/",
                    "$1\n    use WithDynamicLayout;\n",
                    $content,
                    1
                );
                
                // Update render method to add ->layout() if not already present
                // Match: return view('...') or return view('...', [...])
                if (preg_match("/return view\([^)]+\)(?!->layout)/", $content)) {
                    // Add ->layout($this->layout) after the view() call
                    $content = preg_replace(
                        "/(return view\([^)]+\))(;)/",
                        "$1->layout(\$this->layout)$2",
                        $content
                    );
                }
                
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
        $this->info('Done! All components have been updated to use dynamic layouts.');
        
        return Command::SUCCESS;
    }
}
