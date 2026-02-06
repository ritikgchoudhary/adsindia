<?php

namespace App\Http\Controllers\Advertiser;

use App\Http\Controllers\Controller;
use App\Traits\SupportTicketManager;

class TicketController extends Controller
{
    use SupportTicketManager;

    public function __construct()
    {
        parent::__construct();
        $this->layout = 'frontend';
        $this->redirectLink = 'advertiser.ticket.view';
        $this->userType     = 'advertiser';
        $this->column       = 'advertiser_id';
        $this->user         = auth()->guard('advertiser')->user();
        if ($this->user) {
            $this->layout = 'master';
        }
    }
}
