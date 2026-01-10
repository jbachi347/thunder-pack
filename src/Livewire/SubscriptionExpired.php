<?php

namespace ThunderPack\Livewire;

use Livewire\Component;

class SubscriptionExpired extends Component
{
    public function render()
    {
        return view('thunder-pack::livewire.subscription-expired')
            ->layout('layouts.app-sidebar');
    }
}
