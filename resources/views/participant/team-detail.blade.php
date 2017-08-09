@extends('participant.main.master')

@section('title', 'Profile')

@section('style')
    <link href="{{ asset('vendor/x-editable/dist/bootstrap3-editable/css/bootstrap-editable.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/jasny-bootstrap/dist/css/jasny-bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/participant/footer.css') }}" rel="stylesheet">
    <link href="{{ asset('css/participant/tab-pages.css') }}" rel="stylesheet">
    <link href="{{ asset('css/participant/search-input.css') }}" rel="stylesheet">
    <link href="{{ asset('css/participant/modify-table.css') }}" rel="stylesheet">
    <style type="text/css">
        #profile-picture-container {
            height: 150px;
            width: 150px;
            margin: 0px auto 20px auto;
            position: relative;
        }
        #profile-picture-container:hover > * {
            visibility: visible;
        }
        #profile-picture-container > img {
            height: 140px;
            width: 140px;
        }

        #profile-picture-action-container {
            position: absolute;
            top: 66px;
            left: 29px;
            visibility: hidden;
        }
        #profile-picture-action-upload {
            margin-right: 20px;
        }
        #profile-picture-action-upload > i {
            font-size: 24px;
        }
        #profile-picture-action-delete {
            margin-left: 20px;
        }
        #profile-picture-action-delete > i {
            font-size: 24px;
        }

        /* Modified Border Bottom in Editable Javascript */
        .editable-bottom-border-none {
            border-bottom: none !important;
        }

        /* Profile Picture Modal */
        .fileinput-new #btn-file-size-profile-picture {
            width: 150px;
        }
        .fileinput-exists #btn-file-size-profile-picture {
            width: 74px;
        }

        /* Settings Modal */
        .fileinput-new #btn-file-size-settings {
            width: 338px;
        }
        .fileinput-exists #btn-file-size-settings {
            width: 168px;
        }

        .members-rows:first-child {
            margin-bottom: 10px;
        }
        .members-rows+.members-rows {
            margin-bottom: 10px;
        }
        .members-rows:last-child {
            margin-bottom: 0;
        }

        .tournaments-rows:first-child {
            margin-bottom: 10px;
        }
        .tournaments-rows+.tournaments-rows {
            margin-bottom: 10px;
        }
        .tournaments-rows:last-child {
            margin-bottom: 0;
        }

        .table-content-centered th, .table-content-centered td {
            text-align: center;
            vertical-align: middle !important;
        }
    </style>
@endsection

@section('content')
    <div class="container" style="min-height: 536px;">
        <div class="row">
            <div class="col-xs-offset-1 col-xs-2">
                <div id="profile-picture-container" class="thumbnail">
                    @if ($team->picture_file_name)
                        <img id="default-profile-picture" src="{{ asset('storage/team/'.$team->picture_file_name) }}">
                    @else
                        <img id="default-profile-picture" src="{{ asset('img/default-group.png') }}">
                    @endif
                    @if ($inTeam && $isTeamLeader)
                        <div id="profile-picture-action-container">
                            <a role="button" data-toggle="modal" data-target="#profile-picture-modal" id="profile-picture-action-upload"><i class="glyphicon glyphicon-camera"></i></a>
                            <a role="button" id="profile-picture-action-delete"><i class="glyphicon glyphicon-trash"></i></a>
                        </div>
                    @endif
                </div>
            </div>
            <div class="col-xs-8">
                <div style="position: relative;margin-top: 35px;border-bottom: 1px solid #d6d6d6;">
                    <h2>
                        <span id="editable-team-name-value">{{ $team->name }}</span>&nbsp;&nbsp;
                        @if ($inTeam && $isTeamLeader)
                            <a role="button" id="editable-team-name" class="editable-bottom-border-none">
                                <i class="glyphicon glyphicon-pencil"></i>
                            </a>
                        @endif
                    </h2>
                    <h6 style="margin-bottom: 35px; color:#afaeae">Created on {{ date('d F Y', strtotime($team->created_at)) }}</h6>
                    @if ($inTeam)
                        @if ($isTeamLeader)
                            <a role="button" class="btn btn-default" data-toggle="modal" data-target="#team-settings-modal" style="position: absolute;right: 0px;top: 0px;width: 148px;">
                                <i class="glyphicon glyphicon-cog" style="color:#fff"></i>&nbsp;&nbsp;Team Settings
                            </a>
                            <a role="button" class="btn btn-default" data-toggle="modal" data-target="#invite-members-modal" style="position: absolute;right: 0px;top: 40px;width: 148px;">
                                <i class="fa fa-user-plus" aria-hidden="true"></i>&nbsp;&nbsp;Invite Members
                            </a>
                        @else
                            <button class="btn btn-default btn-custom btn-reject" id="btn-leave-team" data-team-name="{{ $team->name }}" style="font-size: 20px;position: absolute;right: 0;top: 10px;">
                                <i class="glyphicon glyphicon-log-out" style="color:#fc5d5d"></i>&nbsp;&nbsp;Leave
                            </button>
                        @endif
                    @else
                        @if ($participant)
                            <div style="position: absolute;right: 0px;top: 10px;">
                                @if (count($team->invitation_list) > 0)
                                    <button class="btn btn-default btn-custom btn-accept accept-invite-request" style="font-size: 20px;" data-team-id="{{ $team->id }}" data-team-name="{{ $team->name }}" data-refresh="true">
                                        <i class="glyphicon glyphicon-ok" style="color:#68ff90"></i>&nbsp;&nbsp;Accept
                                    </button>
                                    @if ($team->join_password)
                                        <button class="btn btn-default btn-custom btn-reject reject-invite-request" style="font-size: 20px;" data-team-id="{{ $team->id }}" data-team-name="{{ $team->name }}" data-refresh="true" data-with-password="true">
                                            <i class="glyphicon glyphicon-remove" style="color:#fc5d5d"></i>&nbsp;&nbsp;Reject
                                        </button>
                                    @else
                                        <button class="btn btn-default btn-custom btn-reject reject-invite-request" style="font-size: 20px;" data-team-id="{{ $team->id }}" data-team-name="{{ $team->name }}" data-refresh="true" data-with-password="false">
                                            <i class="glyphicon glyphicon-remove" style="color:#fc5d5d"></i>&nbsp;&nbsp;Reject
                                        </button>
                                    @endif
                                @else
                                    @if ($team->join_password)
                                        <button class="btn btn-default btn-custom join-with-password" style="font-size: 20px;" data-team-id="{{ $team->id }}" data-team-name="{{ $team->name }}" data-refresh="true">
                                            <i class="glyphicon glyphicon-log-in" style="color:#fff841"></i>&nbsp;&nbsp;Join Team
                                        </button>
                                    @else
                                        <button class="btn btn-default btn-custom join-without-password" style="font-size: 20px;" data-team-id="{{ $team->id }}" data-team-name="{{ $team->name }}" data-refresh="true">
                                            <i class="glyphicon glyphicon-log-in" style="color:#fff841"></i>&nbsp;&nbsp;Join Team
                                        </button>
                                    @endif
                                @endif
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
        <div class="row">
            <div class="well well-lg well-transparent">
                <div class="panel with-nav-tabs panel-default">
                    <ul class="nav nav-tabs">
                        <li class="active"><a href="#teams-tab" data-toggle="tab">Members</a></li>
                        @if ($inTeam)
                            @if ($isTeamLeader)
                                <li><a href="#invited-members-tab" data-toggle="tab">Invited Members</a></li>
                            @endif
                        @endif
                        <li><a href="#schedule-tab" data-toggle="tab">Schedule</a></li>
                        <li><a href="#tournaments-tab" data-toggle="tab">Tournaments</a></li>
                    </ul>
                    <div class="panel-body">
                        <div class="tab-content">
                            <div class="tab-pane fade in active" id="teams-tab">
                                <?php $start = 1; ?>
                                @foreach ($team->details as $detail)
                                    @if ($start % 2 == 1)
                                        <div class="row members-rows">
                                    @endif
                                        <div class="col-xs-6">
                                            @if ($start % 2 == 1)
                                                <div style="padding-left: 16px;padding-right: 8px;">
                                            @else
                                                <div style="padding-left: 8px;padding-right: 16px;">
                                            @endif
                                                <div class="row well-custom" style="padding: 15px 0px;">
                                                    <div class="col-xs-4">
                                                        <div class="thumbnail" style="height: 110px;width: 110px;margin: 0px auto;">
                                                            @if ($detail->picture_file_name)
                                                                <img src="{{ asset('storage/member/'.$detail->picture_file_name) }}" style="height: 100px;width: 100px;">
                                                            @else
                                                                <img src="{{ asset('img/default-profile.jpg') }}" style="height: 100px;width: 100px;">
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-8" style="position: relative;">
                                                        <h3 style="margin-top: 15px;">{{ $detail->name }}</h3>
                                                        <p>{{ $detail->steam32_id ?: '-' }}</p>
                                                        <p style="color: #afaeae;">Joined on {{ date('d F Y', strtotime($detail->created_at)) }}</p>
                                                        @if ($detail->members_privilege == 1 && $inTeam && $isTeamLeader)
                                                            <div style="position: absolute;right: 10px;top: -5px;">
                                                                <a role="button" class="btn-kick-member" data-member-id="{{ $detail->id }}" data-member-name="{{ $detail->name }}">
                                                                    <i class="glyphicon glyphicon-remove-sign" style="font-size: 24px;"></i>
                                                                </a>
                                                            </div>
                                                        @endif
                                                        @if ($detail->members_privilege == 2)
                                                            <p style="position: absolute;right: 5px;bottom: -25px;font-size: 12px;color: #afaeae;"><strong>Captain</strong></p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @if ($start % 2 == 0)
                                        </div>
                                    @endif
                                    <?php $start++; ?>
                                @endforeach
                                @if ($start % 2 == 0)
                                    </div>
                                @endif
                            </div>
                            @if ($inTeam)
                                @if ($isTeamLeader)
                                    <div class="tab-pane fade" id="invited-members-tab">
                                        @if (count($team->invited_members) > 0)
                                            <?php $start = 1; ?>
                                            @foreach ($team->invited_members as $invited_member)
                                                @if ($start % 2 == 1)
                                                    <div class="row members-rows">
                                                @endif
                                                    <div class="col-xs-6">
                                                        @if ($start % 2 == 1)
                                                            <div style="padding-left: 16px;padding-right: 8px;">
                                                        @else
                                                            <div style="padding-left: 8px;padding-right: 16px;">
                                                        @endif
                                                            <div class="row well-custom" style="padding: 15px 0px;">
                                                                <div class="col-xs-4">
                                                                    <div class="thumbnail" style="height: 110px;width: 110px;margin: 0px auto;">
                                                                        @if ($invited_member->parent->member->picture_file_name)
                                                                            <img src="{{ asset('storage/member/'.$invited_member->parent->member->picture_file_name) }}" style="height: 100px;width: 100px;">
                                                                        @else
                                                                            <img src="{{ asset('img/default-profile.jpg') }}" style="height: 100px;width: 100px;">
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                <div class="col-xs-8" style="position: relative;">
                                                                    <h3 style="margin-top: 15px;">{{ $invited_member->parent->member->name }}</h3>
                                                                    <p>{{ $invited_member->parent->member->steam32_id ?: '-' }}</p>
                                                                    <p style="color: #afaeae;">Invited on {{ date('d F Y', strtotime($invited_member->parent->created_at)) }}</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @if ($start % 2 == 0)
                                                    </div>
                                                @endif
                                                <?php $start++; ?>
                                            @endforeach
                                            @if ($start % 2 == 0)
                                                </div>
                                            @endif
                                        @else
                                            <div class="row">
                                                <div class="col-xs-12 text-center" style="opacity: 0.2;">
                                                    <div>
                                                        <i class="fa fa-times" aria-hidden="true" style="font-size: 192px;"></i>
                                                    </div>
                                                    <strong style="font-size: 48px;">Invited Member Not Available</strong>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            @endif
                            <div class="tab-pane fade" id="schedule-tab">
                                @if (count($team->tournaments_schedules) > 0)
                                    <table class="table table-bordered table-striped table-content-centered table-schedule" style="margin-top: 10px;margin-bottom: 0;">
                                        <thead>
                                            <tr>
                                                <th style="width: 276px;">Round</th>
                                                <th style="width: 185px;"></th>
                                                <th style="width: 35px;"></th>
                                                <th style="width: 185px;"></th>
                                                <th style="width: 260px;">Schedule</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($team->tournaments_schedules as $tournaments_schedule)
                                                <tr>
                                                    <td colspan="5" style="font-weight: bold;font-size: 24px;">{{ $tournaments_schedule->tournament->name }}</td>
                                                </tr>
                                                @foreach ($tournaments_schedule->tournament->matches as $match)
                                                    <tr>
                                                        <td>
                                                            @if ($match->round < 0)
                                                                Lower Round {{ abs($match->round) }}
                                                            @elseif ($match->round == 0)
                                                                Bronze Match
                                                            @elseif ($match->round < $tournaments_schedule->tournament->max_round - 1)
                                                                Round {{ $match->round }}
                                                            @elseif ($match->round == $tournaments_schedule->tournament->max_round - 1)
                                                                Semifinals
                                                            @else
                                                                Finals
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if (isset($match->participants[0]))
                                                                {{ $match->participants[0]->team->name }}
                                                            @else
                                                                TBD
                                                            @endif
                                                        </td>
                                                        <td>VS</td>
                                                        <td>
                                                            @if (isset($match->participants[1]))
                                                                {{ $match->participants[1]->team->name }}
                                                            @else
                                                                TBD
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($match->scheduled_time)
                                                                {{ date('l, d F Y H:i:s', strtotime($match->scheduled_time)) }}
                                                            @else
                                                                Not Scheduled
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <div class="row">
                                        <div class="col-xs-12 text-center" style="opacity: 0.2;">
                                            <div>
                                                <i class="fa fa-times" aria-hidden="true" style="font-size: 192px;"></i>
                                            </div>
                                            <strong style="font-size: 64px;">No Tournament Scheduled</strong>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="tab-pane fade" id="tournaments-tab">
                                @if (count($team->tournaments_registrations) > 0)
                                    <?php $start = 1; ?>
                                    @foreach ($team->tournaments_registrations as $tournaments_registration)
                                        @if ($start % 2 == 1)
                                            <div class="row tournaments-rows">
                                        @endif
                                            <div class="col-xs-6">
                                                @if ($start % 2 == 1)
                                                    <div style="padding-left: 16px;padding-right: 8px;">
                                                @else
                                                    <div style="padding-left: 8px;padding-right: 16px;">
                                                @endif
                                                    <div class="row well-custom" style="padding: 15px 0px;">
                                                        <div class="col-xs-4">
                                                            <a href="{{ url('tournament/'.$tournaments_registration->tournament->id) }}" style="width: 110px;height: 110px;">
                                                                <img src="{{ asset('storage/tournament/'.$tournaments_registration->tournament->logo_file_name) }}" style="height: 110px;width: 110px;border: 1px solid black;">
                                                            </a>
                                                        </div>
                                                        <div class="col-xs-8" style="position: relative;">
                                                            <h3 style="margin-top: 15px;">{{ $tournaments_registration->tournament->name }}</h3>
                                                            <p>{{ $tournaments_registration->tournament->owner->name }}</p>
                                                            <p style="color: #afaeae;">{{ date('d F Y', strtotime($tournaments_registration->tournament->start_date)) }} - {{ date('d F Y', strtotime($tournaments_registration->tournament->end_date)) }}</p>
                                                            <p style="position: absolute;right: 5px;bottom: -25px;font-size: 12px;color: #afaeae;">
                                                                <strong>
                                                                    @if ($tournaments_registration->tournament->cancel == 0)
                                                                        @if (date('Y-m-d H:i:s') <= $tournaments_registration->tournament->registration_closed)
                                                                            Upcoming
                                                                        @else
                                                                            @if ($tournaments_registration->tournament->start == 0)
                                                                                Upcoming
                                                                            @else
                                                                                @if ($tournaments_registration->tournament->complete == 0)
                                                                                    In Progress
                                                                                @else
                                                                                    Complete
                                                                                @endif
                                                                            @endif
                                                                        @endif
                                                                    @elseif ($tournaments_registration->tournament->cancel == 1)
                                                                        Cancel
                                                                    @endif
                                                                </strong>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @if ($start % 2 == 0)
                                            </div>
                                        @endif
                                        <?php $start++ ?>
                                    @endforeach
                                    @if ($start % 2 == 0)
                                        </div>
                                    @endif
                                @else
                                    <div class="row">
                                        <div class="col-xs-12 text-center" style="opacity: 0.2;">
                                            <div>
                                                <i class="fa fa-times" aria-hidden="true" style="font-size: 192px;"></i>
                                            </div>
                                            <strong style="font-size: 64px;">No Tournament Registered</strong>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($inTeam && $isTeamLeader)
        <!-- Profile Picture Modal -->
        <div class="modal modal-remove-padding-right" id="profile-picture-modal" tabindex="-1" role="dialog" aria-labelledby="profile-picture-modal-label">
            <div class="modal-dialog modal-dialog-fixed-width-320" role="document">
                <div class="modal-content modal-content-custom">
                    <div class="modal-header modal-header-border-bottom-custom">
                        <h1 class="modal-title modal-title-align-center" id="profile-picture-modal-label">Picture</h1>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-success" role="alert" style="margin-left: 5px;margin-right: 5px;display: none;">
                            <ul id="profile-picture-alert-container">
                                <!-- All message that want to deliver to the user -->
                            </ul>
                        </div>
                        <form id="form-profile-picture" class="form-padding-left-right-15 text-center" enctype="multipart/form-data">
                            <label>Upload Picture</label>
                            <div class="form-group form-group-margin-bottom-0">
                                <div class="fileinput fileinput-new" data-provides="fileinput">
                                    <div class="fileinput-new thumbnail" style="width: 150px; height: 150px;">
                                        @if ($team->picture_file_name)
                                            <img id="default-profile-picture-modal" src="{{ asset('storage/team/'.$team->picture_file_name) }}" style="width: 140px;height: 140px;">
                                        @else
                                            <img id="default-profile-picture-modal" src="{{ asset('img/default-group.png') }}" style="width: 140px;height: 140px;">
                                        @endif
                                    </div>
                                    <div class="fileinput-preview fileinput-exists thumbnail" style="width: 150px; height: 150px;">
                                    </div>
                                    <div>
                                        <span id="btn-file-size-profile-picture" class="btn btn-default btn-file">
                                            <span class="fileinput-new">Browse</span>
                                            <span class="fileinput-exists">Change</span>
                                            <input type="file" name="picture" accept="image/jpeg, image/png" required="required">
                                        </span>
                                        <a href="#" class="btn btn-default btn-custom fileinput-exists" data-dismiss="fileinput" style="width: 74px;float: right;">Remove</a>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group form-group-margin-bottom-0">
                                <button type="submit" class="btn btn-default btn-custom ladda-button" data-style="zoom-out" data-spinner-color="#A9A9A9" id="btn-save-form-profile-picture">
                                    <span class="ladda-label">Save</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Team Settings Modal -->
        <div class="modal modal-remove-padding-right" id="team-settings-modal" tabindex="-1" role="dialog" aria-labelledby="team-settings-modal-label">
            <div class="modal-dialog modal-dialog-fixed-width-320" role="document">
                <div class="modal-content modal-content-custom">
                    <div class="modal-header modal-header-border-bottom-custom">
                        <h1 class="modal-title modal-title-align-center" id="team-settings-modal-label">Team Settings</h1>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-success" role="alert" style="margin-left: 5px;margin-right: 5px;display: none;">
                            <ul id="team-settings-alert-container">
                                <!-- All message that want to deliver to the user -->
                            </ul>
                        </div>
                        <form id="form-team-settings" class="form-padding-left-right-15" enctype="multipart/form-data">
                            <div class="form-inline">
                                <div class="form-group">
                                    <label for="txtbox-join-password" style="margin-right: 10px;">
                                        <i class="fa fa-key" aria-hidden="true"></i>
                                    </label>
                                    @if ($team->join_password)
                                        <input type="text" class="form-control" id="txtbox-join-password" name="join_password" placeholder="Team Join Code" required="required" value="{{ $team->join_password }}" style="width: 230px;">
                                    @else
                                        <input type="text" class="form-control" id="txtbox-join-password" name="join_password" placeholder="Team Join Code" required="required" disabled="disabled" value="" style="width: 230px;">
                                    @endif
                                </div>
                            </div>
                            <div style="text-align: right;margin-top: 10px;">
                                <div class="checkbox" style="margin: 0px 10px 0px 0px;display: inline-block;">
                                    <label>
                                        @if ($team->join_password)
                                            <input type="checkbox" name="with_join_password" id="ckbox-join-password" value="1" checked="checked"> Join Code
                                        @else
                                            <input type="checkbox" name="with_join_password" id="ckbox-join-password" value="1"> Join Code
                                        @endif
                                    </label>
                                </div>
                                <button type="submit" class="btn btn-default btn-custom ladda-button" data-style="zoom-out" data-spinner-color="#A9A9A9" id="btn-save-form-team-settings" style="display: inline-block;">
                                    <span class="ladda-label">Save</span>
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer" style="margin: 0 10px;text-align: center;">
                        <button class="btn btn-default btn-custom btn-reject" id="btn-disband-team" data-team-name="{{ $team->name }}" style="font-size: 20px;">
                            <i class="glyphicon glyphicon-remove" style="color:#fc5d5d"></i>&nbsp;&nbsp;Disband
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invite Members Modal -->
        <div class="modal modal-remove-padding-right" id="invite-members-modal" tabindex="-1" role="dialog" aria-labelledby="invite-members-modal-label">
            <div class="modal-dialog modal-dialog-fixed-width-500" role="document">
                <div class="modal-content modal-content-custom">
                    <div class="modal-header modal-header-border-bottom-custom">
                        <h1 class="modal-title modal-title-align-center" id="invite-members-modal-label">Invite Members</h1>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-xs-10 col-xs-offset-1">
                                <div class="input-group stylish-input-group">
                                    <span class="input-group-addon">
                                        <i class="glyphicon glyphicon-search"></i>
                                    </span>
                                    <input type="text" id="txtbox-invite-members" class="form-control" placeholder="Search name...">
                                </div>
                            </div>
                        </div>
                        <div id="invite-members-list-container" style="margin-top: 15px;padding: 0px 25px;height: 400px;overflow: auto;">
                            <!-- Place Members Result from Search here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('footer')
    @include('participant.footer.footer')
@endsection

@section('script')
    <script src="{{ asset('vendor/x-editable/dist/bootstrap3-editable/js/bootstrap-editable.js') }}"></script>
    <script src="{{ asset('vendor/jasny-bootstrap/dist/js/jasny-bootstrap.min.js') }}"></script>
    @if ($inTeam)
        @if ($isTeamLeader)
            <script src="{{ asset('js/participant/team-detail.js') }}"></script>
        @else
            <script src="{{ asset('js/participant/team-detail-member.js') }}"></script>
        @endif
    @else
        @if ($participant)
            <script src="{{ asset('js/participant/team.js') }}"></script>
        @endif
    @endif
@endsection
