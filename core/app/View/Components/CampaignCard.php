<?php

namespace App\View\Components;

use Illuminate\View\Component;

class CampaignCard extends Component
{
    public $campaign;
    public $loopIteration;

    /**
     * Create a new component instance.
     *
     * @param $campaign
     * @param int $loopIteration
     */
    public function __construct($campaign, $loopIteration = 1)
    {
        $this->campaign = $campaign;
        $this->loopIteration = $loopIteration;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.campaign-card');
    }
}
