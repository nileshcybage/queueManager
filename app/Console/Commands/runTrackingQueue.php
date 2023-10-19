<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\JobController;

class runTrackingQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'runTrackingQueue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'runTrackingQueue';

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
        $controllerObject = new JobController();
        $controllerObject->runQueue();


    }
}
