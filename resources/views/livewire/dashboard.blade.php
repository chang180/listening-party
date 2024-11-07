<?php

use Livewire\Volt\Component;
use Livewire\Volt\Attributes\Validate;
use App\Models\ListeningParty;
use App\Models\Episode;

new class extends Component {
    #[Validate('required|string|max:255')]
    public string $name = '';
    #[Validate('required')]
    public $startTime;
    #[Validate('url|required')]
    public string $mediaUrl = '';

    public function createListeningParty()
    {
        $this->validate();

        // first check that there are not existing episodes with the same URL
        // ust the if there is, oterwise create a new one
        // when a new episode is created , grab information with a background job
        // then use that information to create a new listening party
        // finally redirect to the listening party page

        $episode = Episode::Create([
            'media_url' => $this->mediaUrl,
        ]);

        $listeningParty = ListeningParty::create([
            'name' => $this->name,
            'episode_id' => $episode->id,
            'start_time' => $this->startTime,
        ]);

    }

    public function with()
    {
        return [
            'listening_parties' => ListeningParty::all(),
        ];
    }
}; ?>

<div class="flex items-center justify-center min-h-screen bg-slate-150">
    <div class="w-full max-w-lg px-4">
        <form wire:submit='createListeningParty' class="space-y-6">
            <x-input wire:mode='name' placeholder='Listening Party Name' />
            <x-input wire:model='mediaUrl' placeholder='Podcast Episode URL' description="Direct Eposide Link or YouTube Link, RSS Feeds will grabp the latest episode."/>
            <x-datetime-picker xire:model="startTime" placeholder="Lostening Party Start Time" />
            <x-button primary type="submit">Create Listening Party</x-button>
        </form>
    </div>
</div>