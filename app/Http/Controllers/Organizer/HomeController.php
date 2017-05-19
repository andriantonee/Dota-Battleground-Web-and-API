<?php

namespace App\Http\Controllers\Organizer;

use App\Tournament;
use Illuminate\Http\Request;

class HomeController extends BaseController
{
    public function index(Request $request)
    {
        return view('organizer.home');
    }

    public function dashboard(Request $request)
    {
        $organizer = $request->input('organizer_model');
        $tournaments = Tournament::select('id', 'name', 'logo_file_name', 'type', 'entry_fee', 'registration_closed', 'start_date', 'end_date', 'created_at')
            ->with([
                'approval' => function($approval) {
                    $approval->select('tournaments_id', 'members_id', 'accepted');
                }
            ])
            ->where('members_id', $organizer->id)
            ->whereDoesntHave('approval', function($approval) {
                $approval->where('tournaments_approvals.accepted', 1);
            })
            ->get();

        $tournaments = $tournaments->map(function($tournament, $key) {
            if ($tournament->type == 1) {
                $tournament->type = 'Single Elimination';
            } else if ($tournament->type == 2) {
                $tournament->type = 'Double Elimination';
            }

            return $tournament;
        });

        return view('organizer.dashboard', compact('tournaments'));
    }

    public function password(Request $request)
    {
        return view('organizer.password');
    }
}
