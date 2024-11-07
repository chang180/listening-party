<?php

use Livewire\Volt\Component;
use App\Models\ListeningParty;

new class extends Component {
    public string $name = '';
    public $startTime;

    public function createListeningParty()
    {
        ListeningParty::create([
            'name' => 'New Listening Party',
            'date' => now(),
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
            <x-datetime-picker xire:model="startTime" placeholder="Lostening Party Start Time" />
            <x-button primary>Create Listening Party</x-button>
        </form>
    </div>
</div>
