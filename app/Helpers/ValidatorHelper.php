<?php

namespace App\Helpers;

use App\Member;
use App\Team;
use App\Tournament;
use App\TournamentRegistration;
use Hash;
use Validator;

class ValidatorHelper
{
    public static function validateLoginRequest(array $data)
    {
        $rule = [
            'email' => 'required|string|email',
            'password' => 'required|string',
            'token_in_json' => 'required|integer|in:0,1'
        ];
        $message = [
            'email.required' => 'E-mail is required.',
            'email.string' => 'E-mail must be a string.',
            'email.email' => 'E-mail format is not a valid email address.',
            'password.required' => 'Password is required.',
            'password.string' => 'Password must be a string.',
            'token_in_json.required' => 'Token in JSON parameter is required.',
            'token_in_json.integer' => 'Token in JSON parameter must be an integer.',
            'token_in_json.in' => 'Token in JSON parameter value can support 0 or 1 only.'
        ];
        $validator = Validator::make($data, $rule, $message);

        if ($validator->fails()) {
            return $validator->errors()->all();
        } else {
            return null;
        }
    }

    public static function validateRegisterRequest(array $data, $member_type)
    {
        $rule = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255|email',
            'password' => 'required|string|min:6|confirmed'
        ];
        $message = [
            'name.required' => 'Name is required.',
            'name.string' => 'Name must be a string.',
            'name.max' => 'Name has a maximum :max characters only.',
            'email.required' => 'E-mail is required.',
            'email.string' => 'E-mail must be a string.',
            'email.max' => 'E-mail has a maximum :max characters only.',
            'email.email' => 'E-mail format is not a valid email address.',
            'password.required' => 'Password is required.',
            'password.string' => 'Password must be a string.',
            'password.min' => 'Password must contain minimum :min characters.',
            'password.confirmed' => 'Password confirmation does not match.'
        ];
        $validator = Validator::make($data, $rule, $message);

        if ($validator->fails()) {
            return $validator->errors()->all();
        } else {
            if (Member::checkEmailExists($data['email'], $member_type)) {
                return ['E-mail '.$data['email'].' has been used.'];
            } else {
                return null;
            }
        }
    }

    public static function validateProfileUpdateRequest(array $data, $member_type, $member_id)
    {
        $rule = [
            'name' => 'filled|string|max:255',
            'email' => 'filled|string|max:255|email',
            'steam32_id' => 'string|max:255'
        ];
        $message = [
            'name.filled' => 'Name must not empty.',
            'name.string' => 'Name must be a string.',
            'name.max' => 'Name has a maximum :max characters only.',
            'email.filled' => 'E-mail must not empty.',
            'email.string' => 'E-mail must be a string.',
            'email.max' => 'E-mail has a maximum :max characters only.',
            'email.email' => 'E-mail format is not a valid email address.',
            'steam32_id.string' => 'Steam ID 32-bit must be a string.',
            'steam32_id.max' => 'Steam ID 32-bit has a maximum :max characters only.'
        ];
        $validator = Validator::make($data, $rule, $message);

        if ($validator->fails()) {
            return $validator->errors()->all();
        } else {
            if (array_key_exists('email', $data)) {
                if (Member::checkEmailExists($data['email'], $member_type, $member_id)) {
                    return ['E-mail '.$data['email'].' has been used.'];
                } else {
                    return null;
                }
            } else {
                return null;
            }
        }
    }

    public static function validatePasswordUpdateRequest(array $data, $password)
    {
        $rule = [
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed'
        ];
        $message = [
            'old_password.required' => 'Old Password is required.',
            'old_password.string' => 'Old Password must be a string.',
            'new_password.required' => 'New Password is required.',
            'new_password.string' => 'New Password must be a string.',
            'new_password.min' => 'New Password must contain minimum :min characters.',
            'new_password.confirmed' => 'New Password confirmation does not match.'
        ];
        $validator = Validator::make($data, $rule, $message);

        if ($validator->fails()) {
            return $validator->errors()->all();
        } else {
            if (!Hash::check($data['old_password'], $password)) {
                return ['Old Password is not valid.'];
            } else {
                return null;
            }
        }
    }

    public static function validateProfilePictureUpdateRequest(array $data)
    {
        $rule = [
            'profile_picture_file' => 'required|mimes:jpeg,png|max:512'
        ];
        $message = [
            'profile_picture_file.required' => 'Profile Picture file not found.',
            'profile_picture_file.mimes' => 'Profile Picture file only support jpeg and png file type.',
            'profile_picture_file.max' => 'Profile Picture file has passed :max Kb.'
        ];
        $validator = Validator::make($data, $rule, $message);

        if ($validator->fails()) {
            return $validator->errors()->all();
        } else {
            return null;
        }
    }

    public static function validateIdentificationUpdateRequest(array $data)
    {
        $rule = [
            'identification_file' => 'required|mimes:jpeg,png|max:1024'
        ];
        $message = [
            'identification_file.required' => 'Profile Picture file not found.',
            'identification_file.mimes' => 'Profile Picture file only support jpeg and png file type.',
            'identification_file.max' => 'Profile Picture file has passed :max Kb.'
        ];
        $validator = Validator::make($data, $rule, $message);

        if ($validator->fails()) {
            return $validator->errors()->all();
        } else {
            return null;
        }
    }

    public static function validateTeamCreateRequest(array $data)
    {
        $rule = [
            'picture' => 'filled|mimes:jpeg,png|max:1024',
            'name' => 'required|string|max:255',
            'with_join_password' => 'required|integer|in:0,1',
            'join_password' => 'required_if:with_join_password,1|string|max:255'
        ];
        $message = [
            'picture.filled' => 'Picture file not found.',
            'picture.mimes' => 'Picture file only support jpeg and png file type.',
            'picture.max' => 'Picture file has passed :max Kb.',
            'name.required' => 'Name is required.',
            'name.string' => 'Name must be a string.',
            'name.max' => 'Name has a maximum :max characters only.',
            'with_join_password.required' => 'With Join Password parameter is required.',
            'with_join_password.integer' => 'With Join Password parameter must be an integer.',
            'with_join_password.in' => 'With Join Password parameter value can support 0 or 1 only.',
            'join_password.required_if' => 'Join Code is required.',
            'join_password.string' => 'Join Code must be a string.',
            'join_password.max' => 'Join Code has a maximum :max characters only.'
        ];
        $validator = Validator::make($data, $rule, $message);

        if ($validator->fails()) {
            return $validator->errors()->all();
        } else {
            if (Team::checkNameExists($data['name'])) {
                return ['Name '.$data['name'].' has been used.'];
            } else {
                return null;
            }
        }
    }

    public static function validateTeamUpdateRequest(array $data, $team_id)
    {
        $rule = [
            'name' => 'filled|string|max:255',
            'with_join_password' => 'filled|integer|in:0,1',
            'join_password' => 'required_if:with_join_password,1|string|max:255'
        ];
        $message = [
            'name.filled' => 'Name must not empty.',
            'name.string' => 'Name must be a string.',
            'name.max' => 'Name has a maximum :max characters only.',
            'with_join_password.filled' => 'With Join Password parameter must not empty.',
            'with_join_password.integer' => 'With Join Password parameter must be an integer.',
            'with_join_password.in' => 'With Join Password parameter value can support 0 or 1 only.',
            'join_password.required_if' => 'Join Code must not empty.',
            'join_password.string' => 'Join Code must be a string.',
            'join_password.max' => 'Join Code has a maximum :max characters only.'
        ];
        $validator = Validator::make($data, $rule, $message);

        if ($validator->fails()) {
            return $validator->errors()->all();
        } else {
            if (array_key_exists('name', $data)) {
                if (Team::checkNameExists($data['name'], $team_id)) {
                    return ['Name '.$data['name'].' has been used.'];
                } else {
                    return null;
                }
            } else {
                return null;
            }
        }
    }

    public static function validateTeamPictureUpdateRequest(array $data)
    {
        $rule = [
            'picture' => 'required|mimes:jpeg,png|max:1024'
        ];
        $message = [
            'picture.required' => 'Picture file not found.',
            'picture.mimes' => 'Picture file only support jpeg and png file type.',
            'picture.max' => 'Picture file has passed :max Kb.'
        ];
        $validator = Validator::make($data, $rule, $message);

        if ($validator->fails()) {
            return $validator->errors()->all();
        } else {
            return null;
        }
    }

    public static function validateJoinTeamRequest(array $data, $join_password)
    {
        $rule = [
            'join_password' => 'required|string|max:255'
        ];
        $message = [
            'join_password.required' => 'Join Code must not empty.',
            'join_password.string' => 'Join Code must be a string.',
            'join_password.max' => 'Join Code has a maximum :max characters only.'
        ];
        $validator = Validator::make($data, $rule, $message);

        if ($validator->fails()) {
            return $validator->errors()->all();
        } else {
            if ($data['join_password'] !== $join_password) {
                return ['Join Code is invalid.'];
            } else {
                return null;
            }
        }
    }

    public static function validateTournamentCreateRequest(array $data)
    {
        $rule = [
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:65535',
            'logo' => 'required|mimes:jpeg,png|max:1024',
            'type' => 'required|integer|in:1,2',
            'league_id' => 'integer|min:0|max:4294967295',
            'city' => 'filled|integer|exists:cities,id',
            'address' => 'string|max:255',
            'max_participant' => 'required|integer|min:3|max:256',
            'rules' => 'required|string|max:65535',
            'prize_1st' => 'string|max:255',
            'prize_2nd' => 'string|max:255',
            'prize_3rd' => 'string|max:255',
            'prize_other' => 'string|max:65535',
            'entry_fee' => 'required|integer|min:1|max:999999',
            'registration_closed' => 'required|string|date_format:d/m/Y H:i',
            'upload_identification_card' => 'filled|integer|in:0,1',
            'start_date' => 'required|string|date_format:d/m/Y',
            'end_date' => 'required|string|date_format:d/m/Y'
        ];
        $message = [
            'name.required' => 'Name is required.',
            'name.string' => 'Name must be a string.',
            'name.max' => 'Name has a maximum :max characters only.',
            'description.required' => 'Description is required.',
            'description.string' => 'Description must be a string.',
            'description.max' => 'Description has a maximum :max characters only.',
            'logo.required' => 'Logo file not found.',
            'logo.mimes' => 'Logo file only support jpeg and png file type.',
            'logo.max' => 'Logo file has passed :max Kb.',
            'type.required' => 'Type is required.',
            'type.integer' => 'Type must be an integer.',
            'type.in' => 'Type can support Single Elimination or Double Elimination only.',
            'league_id.integer' => 'League ID must be an integer.',
            'league_id.min' => 'League ID has a minimum :min value.',
            'league_id.max' => 'League ID has a maximum :max value.',
            'city.filled' => 'City must not empty.',
            'city.integer' => 'City must be an integer.',
            'city.exists' => 'City is invalid.',
            'address.string' => 'Address must be a string.',
            'address.max' => 'Address has a maximum :max characters only.',
            'max_participant.required' => 'Max Participant is required.',
            'max_participant.integer' => 'Max Participant must be an integer.',
            'max_participant.min' => 'Max Participant has a minimum :min value.',
            'max_participant.max' => 'Max Participant has a maximum :max value.',
            'rules.required' => 'Rules is required.',
            'rules.string' => 'Rules must be a string.',
            'rules.max' => 'Rules has a maximum :max characters only.',
            'prize_1st.string' => 'Prize 1st must be a string.',
            'prize_1st.max' => 'Prize 1st has a maximum :max characters only.',
            'prize_2nd.string' => 'Prize 2nd must be a string.',
            'prize_2nd.max' => 'Prize 2nd has a maximum :max characters only.',
            'prize_3rd.string' => 'Prize 3rd must be a string.',
            'prize_3rd.max' => 'Prize 3rd has a maximum :max characters only.',
            'prize_other.string' => 'Prize Other must be a string.',
            'prize_other.max' => 'Prize Other has a maximum :max characters only.',
            'entry_fee.required' => 'Entry Fee is required.',
            'entry_fee.integer' => 'Entry Fee must be an integer.',
            'entry_fee.min' => 'Entry Fee has a minimum :min value.',
            'entry_fee.max' => 'Entry Fee has a maximum :max value.',
            'registration_closed.required' => 'Registration Closed is required.',
            'registration_closed.string' => 'Registration Closed must be a string.',
            'registration_closed.date_format' => 'Registration Closed does not match the format :format.',
            'upload_identification_card.filled' => 'Upload Identification Card must not empty.',
            'upload_identification_card.integer' => 'Upload Identification Card must be an integer.',
            'upload_identification_card.in' => 'Upload Identification Card can support 0 or 1 only.',
            'start_date.required' => 'Start Date is required.',
            'start_date.string' => 'Start Date must be a string.',
            'start_date.date_format' => 'Start Date does not match the format :format.',
            'end_date.required' => 'End Date is required.',
            'end_date.string' => 'End Date must be a string.',
            'end_date.date_format' => 'End Date does not match the format :format.'
        ];
        $validator = Validator::make($data, $rule, $message);

        if ($validator->fails()) {
            return $validator->errors()->all();
        } else {
            if (array_key_exists('league_id', $data)) {
                if (Tournament::checkLeagueIDExists($data['league_id'])) {
                    return ['League ID '.$data['league_id'].' has been used.'];
                } else {
                    return null;
                }
            } else {
                return null;
            }
        }
    }

    public static function validateTournamentUpdateRequest(array $data, $tournament_id)
    {
        $rule = [
            'description' => 'required|string|max:65535',
            'league_id' => 'integer|min:0|max:4294967295',
            'city' => 'filled|integer|exists:cities,id',
            'address' => 'string|max:255',
            'prize_1st' => 'string|max:255',
            'prize_2nd' => 'string|max:255',
            'prize_3rd' => 'string|max:255',
            'prize_other' => 'string|max:65535'
        ];
        $message = [
            'description.required' => 'Description is required.',
            'description.string' => 'Description must be a string.',
            'description.max' => 'Description has a maximum :max characters only.',
            'league_id.integer' => 'League ID must be an integer.',
            'league_id.min' => 'League ID has a minimum :min value.',
            'league_id.max' => 'League ID has a maximum :max value.',
            'city.filled' => 'City must not empty.',
            'city.integer' => 'City must be an integer.',
            'city.exists' => 'City is invalid.',
            'address.string' => 'Address must be a string.',
            'address.max' => 'Address has a maximum :max characters only.',
            'prize_1st.string' => 'Prize 1st must be a string.',
            'prize_1st.max' => 'Prize 1st has a maximum :max characters only.',
            'prize_2nd.string' => 'Prize 2nd must be a string.',
            'prize_2nd.max' => 'Prize 2nd has a maximum :max characters only.',
            'prize_3rd.string' => 'Prize 3rd must be a string.',
            'prize_3rd.max' => 'Prize 3rd has a maximum :max characters only.',
            'prize_other.string' => 'Prize Other must be a string.',
            'prize_other.max' => 'Prize Other has a maximum :max characters only.'
        ];
        $validator = Validator::make($data, $rule, $message);

        if ($validator->fails()) {
            return $validator->errors()->all();
        } else {
            if (array_key_exists('league_id', $data)) {
                if ($data['league_id']) {
                    if (Tournament::checkLeagueIDExists($data['league_id'], $tournament_id)) {
                        return ['League ID '.$data['league_id'].' has been used.'];
                    } else {
                        return null;
                    }
                } else {
                    return null;
                }
            } else {
                return null;
            }
        }
    }

    public static function validateTournamentRegisterRequest(array $data, $leader_id, $tournament_id, $must_have_identifications)
    {
        $rule = [
            'team' => 'required|integer',
            'members' => 'required|array|size:5',
            'members.*' => 'required|integer'
        ];
        $message = [
            'team.required' => 'You must select a team.',
            'team.integer' => 'Team must be an integer.',
            'members.required' => 'You must select 5 members in order to participate this tournament.',
            'members.array' => 'Member must be an array.',
            'members.size' => 'You need :size members in order to participate this tournament.',
            'members.*.required' => 'Each member is required.',
            'members.*.integer' => 'Each member must be an integer.'
        ];
        $validator = Validator::make($data, $rule, $message);

        if ($validator->fails()) {
            return $validator->errors()->all();
        } else {
            $team = Team::find($data['team']);
            if ($team) {
                $team_leader = $team->details()->withPivot('members_privilege')->find($leader_id);
                if ($team_leader) {
                    if ($team_leader->pivot->members_privilege == 2) {
                        foreach ($data['members'] as $member_id) {
                            $team_member = $team->details()->find($member_id);
                            if ($team_member) {
                                if ($team_member->steam32_id) {
                                    if ($must_have_identifications) {
                                        if (!$team_member->identifications()->exists()) {
                                            return ['Each member must upload an identity card.'];
                                        }
                                    }
                                } else {
                                    return ['Each member must assign their Steam ID 32-bit.'];
                                }
                            } else {
                                return ['Each member must be a part of the team.'];
                            }
                        }
                    } else {
                        return ['You are not a team leader of this team.'];
                    }
                } else {
                    return ['You are not a part of this team.'];
                }
            } else {
                return ['Team ID is invalid.'];
            }

            if (TournamentRegistration::checkTournamentRegisterExists($team->id, $tournament_id)) {
                return ['The team already registered in this tournament.'];
            } else {
                return null;
            }
        }
    }

    public static function validateTournamentRegisterConfirmationRequest(array $data)
    {
        $rule = [
            'name' => 'required|string|max:255',
            'bank' => 'required|integer|in:1,2',
            'confirmation_file_name' => 'required|mimes:jpeg,png|max:1024'
        ];
        $message = [
            'name.required' => 'Transfer Name is required.',
            'name.string' => 'Transfer Name must be a string.',
            'name.max' => 'Transfer Name has a maximum :max characters.',
            'bank.required' => 'Transfer Bank is required.',
            'bank.integer' => 'Transfer Bank must be an integer.',
            'bank.in' => 'Transfer Bank value can support 1 or 2 only.',
            'confirmation_file_name.required' => 'Proof of Payment file not found.',
            'confirmation_file_name.mimes' => 'Proof of Payment file only support jpeg and png file type.',
            'confirmation_file_name.max' => 'Proof of Payment file has passed :max Kb.'
        ];
        $validator = Validator::make($data, $rule, $message);

        if ($validator->fails()) {
            return $validator->errors()->all();
        } else {
            return null;
        }
    }

    public static function validateTournamentTypeUpdateRequest(array $data)
    {
        $rule = [
            'type' => 'required|integer|in:1,2',
            'randomize' => 'required|integer|in:0,1'
        ];
        $message = [
            'type.required' => 'Type is required.',
            'type.integer' => 'Type must be an integer.',
            'type.in' => 'Type can support Single Elimination or Double Elimination only.',
            'randomize.required' => 'Randomize Parameter is required.',
            'randomize.integer' => 'Randomize Parameter must be an integer.',
            'randomize.in' => 'Randomize Parameter can support 0 or 1 only.'
        ];
        $validator = Validator::make($data, $rule, $message);

        if ($validator->fails()) {
            return $validator->errors()->all();
        } else {
            return null;
        }
    }

    public static function validateMatchScheduleUpdateRequest(array $data)
    {
        $rule = [
            'scheduled_time' => 'required|string|date_format:d/m/Y H:i:s'
        ];
        $message = [
            'scheduled_time.required' => 'Scheduled Time is required.',
            'scheduled_time.string' => 'Scheduled Time must be a string.',
            'scheduled_time.date_format' => 'Scheduled Time does not match the format :format.',
        ];
        $validator = Validator::make($data, $rule, $message);

        if ($validator->fails()) {
            return $validator->errors()->all();
        } else {
            return null;
        }
    }

    public static function validateMatchScoreUpdateRequest(array $data)
    {
        $rule = [
            'side_1_score' => 'required|integer|min:0|max:3',
            'side_2_score' => 'required|integer|min:0|max:3',
            'final_score' => 'required|integer|in:0,1'
        ];
        $message = [
            'side_1_score.required' => 'Player 1 Score is required.',
            'side_1_score.integer' => 'Player 1 Score must be an integer.',
            'side_1_score.min' => 'Player 1 Score has a minimum :min value.',
            'side_1_score.max' => 'Player 1 Score has a maximum :max value.',
            'side_2_score.required' => 'Player 2 Score is required.',
            'side_2_score.integer' => 'Player 2 Score must be an integer.',
            'side_2_score.min' => 'Player 2 Score has a minimum :min value.',
            'side_2_score.max' => 'Player 2 Score has a maximum :max value.',
            'final_score.required' => 'Final Score Parameter is required.',
            'final_score.integer' => 'Final Score Parameter must be an integer.',
            'final_score.in' => 'Final Score Parameter can support 0 or 1 only.'
        ];
        $validator = Validator::make($data, $rule, $message);

        if ($validator->fails()) {
            return $validator->errors()->all();
        } else {
            if ($data['final_score'] == 1) {
                if ($data['side_1_score'] == $data['side_2_score']) {
                    return ['There are no winner for this score. This must not be a final score.'];
                }
            } else {
                return null;
            }
        }
    }
}
