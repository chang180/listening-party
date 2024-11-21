<?php

use Livewire\Volt\Component;
use App\Models\ListeningParty;

new class extends Component {
    public ListeningParty $listeningParty;

    public function mount(ListeningParty $listeningParty)
    {
        $this->listeningParty = $listeningParty->load('episode.podcast');
    }
}; ?>

<div>
    @if ($listeningParty->end_time === null)
        <div class="flex justify-center">
            <button wire:click="endParty" class="px-4 py-2 font-bold text-white bg-red-500 rounded hover:bg-red-700">
                Creating your <span class="font-bold">{{ $listeningParty->name }}</span> listening party
            </button>
        </div>
    @else
        <div x-data="{
            audio: null,
            isLoading: true,
            currentTime: 0,
            startTimestamp: {{ $listeningParty->start_time->timestamp }},

            itializeAudioPlayer() {
                this.audio = this.$refs.audioPlayer;
                this.audio.addEventListener('loadedmetadata', () => {
                    this.isLoading = false;
                    this.checkAndPlayAudio();
                });

                this.audio.addEventListener('timeupdate', () => {
                    this.currentTime = Math.floor(this.audio.currentTime);
                });
            },

            checkandPlayAudio() {
                const elapsedTime = Math.floor(Date.now() / 1000) - this.startTimestamp;
                if (elapsedTime >= 0) {
                    this.audio.currentTime = elapsedTime;
                    this.audio.play().catch(error => console.error('Placback failed:', error));
                    this.isLoading = false;
                } else {
                    setTimeout(() => this.checkAndPlayAutio(), 1000);
                }
            },
            formatTime(seconds) {
                const minutes = Math.floor(seconds / 60);
                const remainingSeconds = Math.floor(seconds % 60);
                return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
            }
        }">
            <audio x-ref="audioPlayer" :src="'{{ $listeningParty->episode->media_url }}'" preload="auto"></audio>
            <div>{{ $listeningParty->episode->podcast->title }}</div>
            <div>{{ $listeningParty->episode->title }}</div>
            <div>Current Time: <span x-text="formatTime(currentTime)"></span></div>
            <div>Start Time: {{ $listeningParty->start_time->toTimeString() }}</div>
            <div x-show="isLoading">Loading...</div>
        </div>
    @endif
</div>
