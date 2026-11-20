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
    protected $signature = 'sync:cloud {--pull : Also pull latest products from cloud}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize local sales and customers to the live cloud server';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Cloud Synchronization...');
        $syncController = app(SyncController::class);

        // 1. Push local sales to cloud
        $res = $syncController->syncToCloud(new Request());
        $data = $res->getData(true);

        if (($data['status'] ?? '') === 'success') {
            $this->info('Push Result: ' . ($data['message'] ?? 'Done'));
        } else {
            $this->error('Push Error: ' . ($data['message'] ?? 'Failed'));
        }

        // 2. Optionally pull products
        if ($this->option('pull')) {
            $this->info('Pulling products from cloud...');
            $pullRes = $syncController->pullProductsFromCloud(new Request());
            $pullData = $pullRes->getData(true);
            $this->info('Pull Result: ' . ($pullData['message'] ?? 'Done'));
        }

        return Command::SUCCESS;
    }
}
