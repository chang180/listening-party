<?php

use Livewire\Volt\Component;

new class extends Component {
    public straing $name = '';
    public $startTime;

    public function createListeningParty()
    {
        ListeningParty::create([
            'name' => 'New Listening Party',
            'date' => now(),
        ]);
    }

    public function with() {
        return [
            'listening_parties' => ListeningParty::all(),
        ];
    }
}; ?>

<div>
    Hello Volt.
</div>
