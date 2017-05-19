<?php

namespace App\Http\Controllers\Admin;

use App\Tournament;
use Illuminate\Http\Request;

class HomeController extends BaseController
{
    public function index()
    {
    	$tournaments = Tournament::select('id', 'name', 'entry_fee', 'registration_closed', 'start_date', 'end_date', 'created_at')
    		->doesntHave('approval')
            ->get();

        return view('admin.home', compact('tournaments'));
    }
}
