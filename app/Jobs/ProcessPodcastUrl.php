<?php

namespace App\Jobs;

use App\Models\Podcast;
use Carbon\CarbonInterval;
use Illuminate\Container\Attributes\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessPodcastUrl implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public $rssUrl,
        public $listeningParty,
        public $episode
    ) {}

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

        $xml = simplexml_load_file($this->rssUrl);

        $podcastTitle = $xml->channel->title;
        $podcastArtworkUrl = $xml->channel->image->url;

        $latestEpisode = $xml->channel->item[0];

        $episodeTitle = $latestEpisode->title;
        $episodeMediaUrl = (string) $latestEpisode->enclosure['url'];

        // register the itunes namespace to grab the duration
        $namespace = $xml->getNamespaces(true);
        $itunesNamespace = $namespace['itunes'] ?? null;

        $episodeLength = null;

        // Try to get duration from Tunes namespace
        if ($itunesNamespace) {
            $episodeLength = $latestEpisode->children($itunesNamespace)->duration;
        }

        // If iTunes namespace is not available or duration is empty try to calculate duration from enclosure length
        if (empty($episodeLength)) {
            $fileSize = (int) $latestEpisode->enclosure['length'];
            $vitrate = 12800; // Assume 128 kbps bitrate
            $durationInSeconds = ceil($fileSize * 8 / $vitrate);
            $episodeLength = (string) $durationInSeconds;
        }

        // Parse the duration
        try {
            if (strpos($episodeLength, ':') === false) {
                // Duration is in HH:MM:SS or MM:SS format
                $parts = explode(':', $episodeLength);
                if (count($parts) === 2) {
                    $interval = CarbonInterval::createFromFormat('i:s', $episodeLength);
                } elseif (count($parts) === 3) {
                    $interval = CarbonInterval::createFromFormat('H:i:s', $episodeLength);
                } else {
                    throw new \Exception('Unexpected duration format');
                }
            } else {
                // Duration is in seconds
                $interval = CarbonInterval::seconds((int) $episodeLength);
            }
        } catch (\Exception $e) {
            // Log the error
            Log::error('Error parsing episode duration: ' . $e->getMessage());
            // Set the duration to 0
            $interval = CarbonInterval::hour(); // Default to 1 hour if parsing fails
        }

        $endTime = $this->listeningParty->start_time->add($interval);

        // save these to the database
        // create the Podcast, and then update the episode to be linked to the podcast
        $podcast = Podcast::updateOrCreate([
            'title' => $podcastTitle,
            'artwork_url' => $podcastArtworkUrl,
            'rss_url' => $this->rssUrl,
        ]);

        $this->episode->podcast()->associate($podcast);

        $this->episode->update([
            'title' => $episodeTitle,
            'media_url' => $episodeMediaUrl,
        ]);

        $this->listeningParty->update([
            'end_time' => $endTime,
        ]);
    }
}
