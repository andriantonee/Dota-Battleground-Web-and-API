<?php

namespace App\Http\Controllers\Admin;

use App\Tournament;
use Illuminate\Http\Request;

class HomeController extends BaseController
{
    public function index()
    {
        $tournaments = Tournament::select('id', 'name', 'entry_fee', 'registration_closed', 'start_date', 'end_date', 'members_id', 'created_at')
            ->with([
                'owner' => function($owner) {
                    $owner->select('id', 'name');
                },
                'approval' => function($approval) {
                    $approval->select('tournaments_id', 'accepted');
                }
            ])
            // ->doesntHave('approval')
            ->get();

        return view('admin.home', compact('tournaments'));
    }
}
