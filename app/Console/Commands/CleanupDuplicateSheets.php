<?php

namespace App\Console\Commands;

use App\Models\Sheet;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupDuplicateSheets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sheets:cleanup-duplicates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up duplicate sheet names within the same file by renaming them';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting cleanup of duplicate sheets...');

        try {
            DB::beginTransaction();

            // Find all files with duplicate sheet names
            $duplicates = DB::table('sheets')
                ->select('file_id', 'name', DB::raw('COUNT(*) as count'))
                ->groupBy('file_id', 'name')
                ->having('count', '>', 1)
                ->get();

            if ($duplicates->isEmpty()) {
                $this->info('No duplicate sheets found.');
                return 0;
            }

            $this->info("Found {$duplicates->count()} duplicate sheet name groups.");

            foreach ($duplicates as $duplicate) {
                $this->info("Processing duplicates for file_id: {$duplicate->file_id}, name: {$duplicate->name}");

                // Get all sheets with this name in this file
                $sheets = Sheet::where('file_id', $duplicate->file_id)
                    ->where('name', $duplicate->name)
                    ->orderBy('id')
                    ->get();

                // Keep the first one as is, rename the rest
                $firstSheet = $sheets->first();
                $this->info("Keeping sheet ID {$firstSheet->id} with original name '{$firstSheet->name}'");

                for ($i = 1; $i < $sheets->count(); $i++) {
                    $sheet = $sheets[$i];
                    $newName = $duplicate->name . ' (' . $i . ')';
                    
                    // Check if the new name already exists
                    $counter = 1;
                    while (Sheet::where('file_id', $duplicate->file_id)->where('name', $newName)->exists()) {
                        $newName = $duplicate->name . ' (' . ($i + $counter) . ')';
                        $counter++;
                    }

                    $sheet->update(['name' => $newName]);
                    $this->info("Renamed sheet ID {$sheet->id} to '{$newName}'");
                }
            }

            DB::commit();
            $this->info('Duplicate sheet cleanup completed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error during cleanup: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
