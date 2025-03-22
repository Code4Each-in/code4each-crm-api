<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ClearCaches extends Command
{
    protected $signature = 'cache:clear-all';
    protected $description = 'Clear all caches in the Laravel application';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Clearing application cache...');
        Artisan::call('cache:clear');

        $this->info('Clearing configuration cache...');
        Artisan::call('config:clear');

        $this->info('Clearing route cache...');
        Artisan::call('route:clear');

        $this->info('Clearing view cache...');
        Artisan::call('view:clear');

        $this->info('Optimizing class loader...');
        Artisan::call('optimize');

        $this->info('All caches cleared successfully.');
    }
}
