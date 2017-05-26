<?php

namespace App\Http\Controllers\Organizer;

use App\Helpers\ValidatorHelper;
use App\Match;
use DB;
use Illuminate\Http\Request;

class MatchController extends BaseController
{
    public function updateSchedule($id, Request $request)
    {
        $match = Match::find($id);
        $organizer = $request->user();
        if ($match) {
            if ($match->tournament->owner()->find($organizer->id)) {
                // Continue
            } else {
                return response()->json(['code' => 404, 'message' => ['Member is not an owner of this Tournament']]);
            }
        } else {
            return response()->json(['code' => 404, 'message' => ['Match ID is invalid.']]);
        }

        $data = [
            'scheduled_time' => $request->input('schedule_date_and_time')
        ];

        if (!$validatorResponse = ValidatorHelper::validateMatchScheduleUpdateRequest($data)) {
            DB::beginTransaction();
            try {
                $match->scheduled_time = date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $data['scheduled_time'])));
                $match->save();

                DB::commit();
                return response()->json(['code' => 200, 'message' => ['Match Scheduled Time has been updated.']]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['code' => 500, 'message' => ['Something went wrong. Please try again.']]);
            }
        } else {
            return response()->json(['code' => 400, 'message' => $validatorResponse]);
        }
    }
}
