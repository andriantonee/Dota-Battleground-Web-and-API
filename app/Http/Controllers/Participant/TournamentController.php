<?php

namespace App\Http\Controllers\Participant;

use Illuminate\Http\Request;

class TournamentController extends BaseController
{
    public function index()
    {
        return view('participant.tournament');
    }

    public function show()
    {
        return view('participant.tournament-detail');
    }
}
