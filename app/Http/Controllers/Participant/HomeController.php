<?php

namespace App\Http\Controllers\Participant;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        return view('participant.home');
    }
}
