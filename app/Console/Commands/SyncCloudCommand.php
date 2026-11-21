<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\SyncController;
use Illuminate\Http\Request;

class SyncCloudCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:cloud {--push-only : Only push local sales without pulling} {--pull : Also pull latest products from cloud}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Two-way synchronization of sales, products, and inventory between local and cloud';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Two-Way Cloud Synchronization...');
        $syncController = app(SyncController::class);

        if ($this->option('push-only')) {
            $res = $syncController->syncToCloud(new Request());
            $data = $res->getData(true);
            $this->info('Push Result: ' . ($data['message'] ?? 'Done'));
            return Command::SUCCESS;
        }

        // Run full two-way sync
        $res = $syncController->syncTwoWay(new Request());
        $data = $res->getData(true);

        if (($data['status'] ?? '') === 'success') {
            $this->info($data['message'] ?? 'Synchronization completed successfully!');
        } else {
            $this->error('Sync Error: ' . ($data['message'] ?? 'Failed'));
        }

        return Command::SUCCESS;
    }
}
