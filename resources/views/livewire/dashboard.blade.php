<?php

use Livewire\Volt\Component;
use Livewire\Volt\Attributes\Validate;
use App\Models\ListeningParty;
use App\Models\Episode;
use App\Jobs\ProcessPodcastUrl;

new class extends Component {
    #[Validate]
    public string $name = '';
    #[Validate]
    public $startTime;
    #[Validate]
    public string $mediaUrl = '';

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'startTime' => 'required',
            'mediaUrl' => 'required|url',
        ];
    }

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

        ProcessPodcastUrl::dispatch($this->mediaUrl, $listeningParty, $episode);

        return redirect()->route('parties.show', $listeningParty);
    }

    public function with()
    {
        return [
            'listeningParties' => ListeningParty::where('is_active', true)->orderBy('start_time', 'asc')->with('episode.podcast')->get(),
        ];
    }
}; ?>

<div class="flex flex-col min-h-screen pt-8 bg-emeral-50">
    {{-- Top Half: Create New Listening Party Form --}}
    <div class="flex items-center justify-center p-4">
        <div class="w-full max-w-lg">
            <x-card shadow="lg" rounded="lg">
                <h2 class="font-serif text-xl text-center fond-bold">Let's listen together. </h2>
                <form wire:submit='createListeningParty' class="mt-6 space-y-6">
                    <x-input wire:model='name' placeholder='Listening Party Name' />
                    <x-input wire:model='mediaUrl' placeholder='Podcast RSS Feed URL'
                        description="Entering the RSS Feeds will grabp the latest episode." />
                    <x-datetime-picker wire:model="startTime" placeholder="Lostening Party Start Time"
                        :min="now()->subDays(1)" />
                    <x-button primary type="submit" class="w-full">Create Listening Party</x-button>
                </form>
            </x-card>
        </div>
    </div>

    {{-- Bottom Half: Active Listening Parties --}}
    <div class="my-20">
        <div class="max-w-lg mx-auto">
            <h3 class="mb-8 font-serif text-lg font-bold">Ongoing Listening Parties</h3>
            <div class="bg-white rounded-lg shadow-lg">
                @if ($listeningParties->isEmpty())
                    <div>No awwdio listening parties started yet ... 😞</div>
                @else
                    @foreach ($listeningParties as $listeningParty)
                        <div wire:key='"{{ $listeningParty->id }}'>
                            <a href="{{ route('parties.show', $listeningParty) }}" class="block">
                                <div
                                    class="flex items-center justify-between p-4 transition-all duration-150 ease-in-out border-b border-gray-200 hover:bg_gray-50">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-shrink-0">
                                            <x-avatar src="{{ $listeningParty->episode->podcast->artwork_url }}"
                                                size="xl" rounded="sm" alt="Podcast Artwork" />
                                        </div>
                                        <div class="flex-1 space-x-4">
                                            <p class="text-[0.9rem] font-semibold text-slate-900">{{ $listeningParty->name }}</p>
                                            <div class="mt-1 text-xs">
                                            <p class="text-sm truncate text-slate-600">{{ $listeningParty->episode->title }}</p>
                                            <p class="text-[0.7rem] tracking-tighter uppercase text-slate-400">{{ $listeningParty->podcast->title }}</p>
                                            </div>
                                            <p>{{ $listeningParty->start_time }}</p>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

</div>
