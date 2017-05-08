<?php

namespace App\Http\Controllers\Organizer;

use Illuminate\Http\Request;

class TournamentController extends BaseController
{
    public function index()
    {
        return view('organizer.tournament');
    }

    public function create()
    {
        return view('organizer.tournament-create');
    }

    public function detail()
    {
        return view('organizer.tournament-detail');
    }
}
