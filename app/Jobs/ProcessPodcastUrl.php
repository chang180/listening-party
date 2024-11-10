<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessPodcastUrl implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public $rssUrl
    )
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // grab the podcast name information
        // grab the latest episode
        // add the latest episode media url to the existing episode
        // update the existing episode's medial url to the latest episode's media url
        // find the episodes lenght and set the listening end_time to the start_time + length of the episode


    }
}
