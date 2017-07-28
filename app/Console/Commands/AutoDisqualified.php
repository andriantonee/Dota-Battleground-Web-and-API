<?php

namespace App\Console\Commands;

use App\Helpers\GuzzleHelper;
use App\Match;
use Carbon;
use DB;
use Illuminate\Console\Command;

class AutoDisqualified extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:disqualified';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto Checking User Attendance & Disqualified.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        set_time_limit(0);

        $end = date('Y-m-d H:i:00');
        $start = date('Y-m-d H:i:00', strtotime($end) - 60);
        $matches = Match::select('*')
            ->whereBetween('scheduled_time', [$start, $end])
            ->whereHas('participants', function($participants) {
                $participants->whereNull('matches_result');
            })
            ->get();

        foreach ($matches as $match) {
            DB::beginTransaction();
            try {
                $player_1 = null;
                $player_2 = null;

                $tournament = $match->tournament;
                $qr_identifier_attendances = $match->attendances()->pluck('qr_identifier')->toArray();
                $participants = $match->participants()
                    ->select('tournaments_registrations.id AS id', 'tournaments_registrations.disqualification AS disqualification', 'tournaments_registrations.challonges_participants_id AS challonges_participants_id', 'matches_participants.side AS side')
                    ->whereIn('matches_participants.side', [1, 2])
                    ->get();
                foreach ($participants as $participant) {
                    if ($participant->side == 1) {
                        $player_1 = $participant;
                    } else if ($participant->side == 2) {
                        $player_2 = $participant;
                    }

                    if ($participant->disqualification == 0) {
                        $qr_identifier = $participant->members()->pluck('qr_identifier')->toArray();
                        $qr_identifier_not_attend = array_diff($qr_identifier, $qr_identifier_attendances);
                        if ($qr_identifier_not_attend) {
                            $participant->disqualification = 1;
                            $participant->save();
                        }
                    }
                }

                if ($player_1 && $player_2) {
                    $winner_id = null;
                    $player_1_score = 0;
                    $player_2_score = 0;
                    $player_1_result = 1;
                    $player_2_result = 1;
                    if ($player_1->disqualification == 1) {
                        $winner_id = $player_2->challonges_participants_id;
                        $player_2_score = 1;
                        $player_2_result = 3;
                    } else if ($player_2->disqualification == 1) {
                        $winner_id = $player_1->challonges_participants_id;
                        $player_1_score = 1;
                        $player_1_result = 3;
                    }

                    if ($winner_id) {
                        if ($tournament->challonges_id) {
                            if ($match->challonges_match_id) {
                                $scores_csv = $player_1_score.'-'.$player_2_score;
                                $success = GuzzleHelper::updateTournamentMatchScore($tournament, $match, $scores_csv, $winner_id);
                            } else {
                                $success = true;
                            }
                        } else {
                            $success = true;
                        }

                        if ($success) {
                            $match->participants()->updateExistingPivot($player_1->id, [
                                'score' => $player_1_score,
                                'matches_result' => $player_1_result
                            ]);
                            $match->participants()->updateExistingPivot($player_2->id, [
                                'score' => $player_2_score,
                                'matches_result' => $player_2_result
                            ]);

                            if ($player_1_result && $player_2_result) {
                                $match->load([
                                    'parents' => function($parents) {
                                        $parents->select('matches.id', 'matches_qualifications_details.from_child_matches_result AS from_child_matches_result', 'matches_qualifications_details.side AS side');
                                    }
                                ]);

                                foreach ($match->parents as $parent) {
                                    if ($player_1_result == $parent->from_child_matches_result) {
                                        $parent->participants()->attach($player_1->id, [
                                            'side' => $parent->side,
                                            'created_at' => Carbon::now(),
                                            'updated_at' => Carbon::now()
                                        ]);
                                    } else if ($player_2_result == $parent->from_child_matches_result) {
                                        $parent->participants()->attach($player_2->id, [
                                            'side' => $parent->side,
                                            'created_at' => Carbon::now(),
                                            'updated_at' => Carbon::now()
                                        ]);
                                    }
                                }
                            }
                        }
                    }

                    DB::commit();
                } else {
                    DB::rollBack();
                }
            } catch (\Exception $e) {
                DB::rollBack();
            }
        }
    }
}
