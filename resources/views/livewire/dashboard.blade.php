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
            'listeningParties' => ListeningParty::where('is_active', true)->whereNotNull('end_time')->orderBy('start_time', 'asc')->with('episode.podcast')->get(),
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
                    <x-datetime-picker wire:model="startTime" placeholder="Listening Party Start Time" :min="now()->subDays(1)"
                        requires-confirmation />
                    <x-button primary type="submit" class="w-full">Create Listening Party</x-button>
                </form>
            </x-card>
        </div>
    </div>

    {{-- Bottom Half: Active Listening Parties --}}
    <div class="my-20">
        <div class="max-w-lg mx-auto">
            <h3 class="mb-4 font-serif text-[0.9rem] font-bold">Upcoming Listening Parties</h3>
            <div class="bg-white rounded-lg shadow-lg">
                @if ($listeningParties->isEmpty())
                    <div class="flex items-center justify-center p-6 font-serif text-sm">No awwdio listening parties
                        started yet ... 😞</div>
                @else
                    @foreach ($listeningParties as $listeningParty)
                        <div wire:key="{{ $listeningParty->id }}">
                            <a href="{{ route('parties.show', $listeningParty) }}" class="block">
                                <div
                                    class="flex items-center justify-between p-4 transition-all duration-150 ease-in-out border-b border-gray-200 hover:bg-gray-50">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-shrink-0">
                                            <x-avatar src="{{ $listeningParty->episode->podcast->artwork_url }}"
                                                size="xl" rounded="sm" alt="Podcast Artwork" />
                                        </div>
                                        <div class="flex-1 space-x-4">
                                            <p class="text-[0.9rem] font-semibold text-slate-900">
                                                {{ $listeningParty->name }}</p>
                                            <div class="mt-1 text-xs">
                                                <p class="max-w-xs text-sm truncate text-slate-600">
                                                    {{ $listeningParty->episode->title }}</p>
                                                <p class="text-[0.7rem] tracking-tighter uppercase text-slate-400">
                                                    {{ $listeningParty->podcast->title }}</p>
                                            </div>
                                            <div class="mt-1 text-xs text-slate-600" x-data="{
                                                startTime: '{{ $listeningParty->start_time->toIso8601String() }}',
                                                countdownText: '',
                                                isLive: {{ $listeningParty->start_time->isPast() && $listeningParty->is_active ? 'true' : 'false' }},
                                                updateCountdown() {
                                                    const start = new Date(this.startTime).getTime();
                                                    const now = new Date().getTime();
                                                    const distance = start - now;
                                                    if (distance < 0) {
                                                        this.countdownText = 'Started';
                                                        this.isLive = true;
                                                    } else {
                                                        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                                                        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                                                        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                                                        const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                                                        this.countdownText = `${days}d ${hours}h ${minutes}m ${seconds}s`;
                                                    }
                                                }
                                            }"
                                                x-init="updateCountdown();
                                                setInterval(
                                                    () => updateCountdown(),
                                                    1000
                                                );">
                                                <div x-show="isLive">
                                                    <x-badge flat rose label="Live">
                                                        <x-slot name="prepend"
                                                            class="relative flex items-center w-2 h-2">
                                                            <span
                                                                class="absolute inline-flex w-full h-full rounded-full opacity-75 bg-rose-500 animate-ping"></span>
                                                            <span
                                                                class="relative inline-flex w-2 h-2 rounded-full bg-rose-500"></span>
                                                        </x-slot>
                                                    </x-badge>
                                                </div>
                                                <div x-show="!isLive">
                                                    Starts in: <span x-text="countdownText"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <x-button flat xs class="w-20">Join</x-button>
                                </div>
                            </a>
                            @if ($listeningParties->isEmpty())
                                <div>No listening parties available</div>
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

</div>
