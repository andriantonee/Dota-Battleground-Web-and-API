<?php

namespace App\Http\Controllers\Organizer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        return view('organizer.home');
    }

    public function dashboard(Request $request)
    {
        return view('organizer.dashboard');
    }

    public function password(Request $request)
    {
    	return view('organizer.password');
    }
}
