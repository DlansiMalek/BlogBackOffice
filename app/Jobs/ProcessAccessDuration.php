<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\AccessServices;
use App\Services\CongressServices;

class ProcessAccessDuration implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $access;

    public function __construct($access)
    {
        $this->access = $access;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(AccessServices $accessServices, CongressServices $congressServices)
    {
        $accessServices->updateUserAccessDuration($this->access->access_id, $this->access->end_date);
        $congressServices->updateUserCongressDuration($this->access->congress_id, $this->access);
    }
}
