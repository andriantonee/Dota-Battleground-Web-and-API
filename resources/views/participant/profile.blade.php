@extends('participant.main.master')

@section('title', 'Profile')

@section('style')
    <link href="{{ asset('vendor/x-editable/dist/bootstrap3-editable/css/bootstrap-editable.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/jasny-bootstrap/dist/css/jasny-bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/participant/footer.css') }}" rel="stylesheet">
    <link href="{{ asset('css/participant/tab-pages.css') }}" rel="stylesheet">
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

        div.popover.editable-container.editable-popup {
            background-color: #292E3A;
            border: 1px solid transparent;
            box-shadow: 0 5px 15px rgba(0,0,0,.5);
            -webkit-box-shadow: 0 5px 15px rgba(0,0,0,.5);
        }
        div.popover.editable-container.editable-popup > .arrow:after {
            border-bottom-color: #292E3A;
        }
        div.popover.editable-container.editable-popup h3.popover-title {
            background-color: #292E3A;
            color: #D8D8D8;
        }
        div.popover.editable-container.editable-popup button.editable-cancel {
            background : linear-gradient(to bottom, #ba1f1f, #ba3f3f);
            border-color: #ba3f3f;
        }
        div.popover.editable-container.editable-popup button.editable-submit {
            background : linear-gradient(to bottom, #1f872b, #20bc36);
            border-color: #31aa53;
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

        .team-list-content {
            display: block;
        }
        .team-list-content:first-child {
            margin-bottom: 10px;
        }
        .team-list-content+.team-list-content {
            margin-bottom: 10px;
        }
        .team-list-content:last-child {
            margin-bottom: 0;
        }

        .table-content-centered th, .table-content-centered td {
            text-align: center;
            vertical-align: middle !important;
        }

        .tournaments-registrations-container {
            margin-bottom: 15px;
        }
        .tournaments-registrations-container:last-child {
            margin-bottom: 0;
        }

        .tournaments-registrations-rows:first-child {
            margin-bottom: 10px;
        }
        .tournaments-registrations-rows+.tournaments-registrations-rows {
            margin-bottom: 10px;
        }
        .tournaments-registrations-rows:last-child {
            margin-bottom: 0;
        }
        .accept{
            color:#20bc36;
        }
        .pending {
            color : #5F89A3;
        }
        .reject {
            color : #ba3f3f;
        }
        .not-confirmed{
            color: #fc7b67;
        }
        .in-progress-tournaments-rows:first-child {
            margin-bottom: 10px;
        }
        .in-progress-tournaments-rows+.in-progress-tournaments-rows {
            margin-bottom: 10px;
        }
        .in-progress-tournaments-rows:last-child {
            margin-bottom: 0;
        }

        #barcode-img {
            height: 165px;
            width: 165px;
        }

        .completed-tournaments-rows:first-child {
            margin-bottom: 10px;
        }
        .completed-tournaments-rows+.completed-tournaments-rows {
            margin-bottom: 10px;
        }
        .completed-tournaments-rows:last-child {
            margin-bottom: 0;
        }
    </style>
@endsection

@section('content')
    <div class="container" style="min-height: 536px;">
        <div class="row">
            <div class="col-xs-offset-1 col-xs-2">
                <div id="profile-picture-container" class="thumbnail">
                    @if ($participant->picture_file_name)
                        <img id="default-profile-picture" src="{{ asset('storage/member/'.$participant->picture_file_name) }}">
                    @else
                        <img id="default-profile-picture" src="{{ asset('img/default-profile.jpg') }}">
                    @endif
                    <div id="profile-picture-action-container">
                        <a role="button" data-toggle="modal" data-target="#profile-picture-modal" id="profile-picture-action-upload"><i class="glyphicon glyphicon-camera"></i></a>
                        <a role="button" id="profile-picture-action-delete"><i class="glyphicon glyphicon-trash"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-xs-8">
                <div style="position: relative;margin-top: 35px;border-bottom: 1px solid #d6d6d6;">
                    <h2>
                        <span id="editable-name-value">{{ $participant->name }}</span>&nbsp;&nbsp;
                        <a role="button" id="editable-name" class="editable-bottom-border-none">
                            <i class="glyphicon glyphicon-pencil"></i>
                        </a>
                    </h2>
                    <h4>
                        <span id="editable-email-value" style="color: #afaeae;">{{ $participant->email }}</span>&nbsp;&nbsp;
                        <a role="button" id="editable-email" class="editable-bottom-border-none">
                            <i class="glyphicon glyphicon-pencil"></i>
                        </a>
                    </h4>
                    <h6>
                        <span id="editable-steam32_id-value" style="color: #afaeae;">{{ $participant->steam32_id ?: '-' }}</span>&nbsp;&nbsp;
                        <a role="button" id="editable-steam32_id" class="editable-bottom-border-none">
                            <i class="glyphicon glyphicon-pencil"></i>
                        </a>
                    </h6>
                    <a role="button" class="btn btn-default" data-toggle="modal" data-target="#settings-modal" style="position: absolute;right: 0;top: 30px;">
                        <i class="glyphicon glyphicon-cog" style="color:#fff"></i>&nbsp;&nbsp;Settings
                    </a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="well well-lg well-transparent">
                <div class="panel with-nav-tabs panel-default" style="border: none;">
                    <ul class="nav nav-tabs">
                        <li class="active"><a href="#teams-tab" data-toggle="tab">Teams</a></li>
                        <li><a href="#schedule-tab" data-toggle="tab">Schedule</a></li>
                        <li><a href="#registration-status-tab" data-toggle="tab">Registration Status</a></li>
                        <li><a href="#in-progress-tournaments-tab" data-toggle="tab">In Progress Tournaments</a></li>
                        <li><a href="#completed-tournaments-tab" data-toggle="tab">Completed Tournaments</a></li>
                        <li><a href="#cancelled-tournaments-tab" data-toggle="tab">Cancelled Tournaments</a></li>
                    </ul>
                    <div class="panel-body">
                        <div class="tab-content">
                            <div class="tab-pane fade in active" id="teams-tab">
                                <div id="team-list-container">
                                    @foreach ($teams as $team)
                                        <a class="team-list-content well-custom" href="{{ url('/team/'.$team->id) }}">
                                            <div class="row" style="padding: 15px 5px;margin: 0;">
                                                <div class="col-xs-2">
                                                    <div class="thumbnail" style="margin: 0px auto;width: 75px;height: 75px;">
                                                        <img src="{{ asset($team->picture_file_name ? '/storage/team/'.$team->picture_file_name : 'img/default-group.png') }}" style="width: 65px;height: 65px;">
                                                    </div>
                                                </div>
                                                <div class="col-xs-10">
                                                    <h3 style="margin-top: 12px;">{{ $team->name }}</h3>
                                                    <h5 style="color: #afaeae;">{{ $team->details_count }} Member</h5>
                                                </div>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                                <div style="margin: 0;margin-top: 25px;text-align: center;">
                                    <a role="button" class="btn btn-default btn-custom" data-toggle="modal" data-target="#create-team-modal" >
                                        <div>
                                            <i class="glyphicon glyphicon-plus-sign" style="font-size: 24px;color:#fff"></i>
                                        </div>
                                        <span style="width: 100%;text-align: center;">Create New Team</span>
                                    </a>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="schedule-tab">
                                @if (count($schedules) > 0)
                                    <table class="table table-bordered table-striped table-content-centered table-schedule" style="margin: 0;">
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
                                            @foreach ($schedules as $tournaments_schedule)
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
                            <div class="tab-pane fade" id="registration-status-tab">
                                @if (count($registrations) > 0)
                                    @foreach ($registrations as $team)
                                        <h4 style="border-bottom: 1px solid #e3e3e3; margin-bottom: 15px;margin-top: 0;padding-bottom: 10px;">{{ $team->name }}</h4>
                                        <div class="tournaments-registrations-container">
                                            <?php $start = 1; ?>
                                            @foreach ($team->tournaments_registrations as $tournaments_registration)
                                                @if ($start % 2 == 1)
                                                    <div class="row tournaments-registrations-rows">
                                                @endif
                                                    <div class="col-xs-6">
                                                        @if ($start % 2 == 1)
                                                            <div style="padding-left: 16px;padding-right: 8px;">
                                                        @else
                                                            <div style="padding-left: 8px;padding-right: 16px;">
                                                        @endif
                                                            <div class="row well-custom" style="padding: 15px 0px;min-height: 137px;max-height: 137px;">
                                                                <div class="col-xs-12" style="position: relative;">
                                                                    <h3 style="margin: 0;">{{ $tournaments_registration->tournament->name }}</h3>
                                                                    <p style="margin: 0;padding-left: 10px;">{{ $tournaments_registration->members_count }} Players</p>
                                                                    <p style="color: #afaeae;margin: 0;margin-bottom: 5px;padding-left: 10px;">Register on {{ date('d F Y', strtotime($tournaments_registration->created_at)) }}</p>
                                                                    @if (!$tournaments_registration->confirmation)
                                                                        <a class="btn btn-default ladda-button btn-custom" href="{{ url('/tournament/confirm-payment/'.$tournaments_registration->id) }}">Payment Confirmation</a>
                                                                    @else
                                                                        @if ($tournaments_registration->confirmation->approval)
                                                                            @if ($tournaments_registration->confirmation->approval->status == 0)
                                                                                <a class="btn btn-default ladda-button btn-custom" href="{{ url('/tournament/confirm-payment/'.$tournaments_registration->id) }}">Payment Confirmation</a>
                                                                            @endif
                                                                        @else
                                                                            <a class="btn btn-default ladda-button btn-custom" href="{{ url('/tournament/confirm-payment/'.$tournaments_registration->id) }}">Payment Confirmation</a>
                                                                        @endif
                                                                    @endif
                                                                    <div class="text-center" style="position: absolute;top: 16px;right: 25px;width: 100px;">
                                                                        @if ($tournaments_registration->confirmation)
                                                                            @if ($tournaments_registration->confirmation->approval)
                                                                                @if ($tournaments_registration->confirmation->approval->status == 1)
                                                                                    <i class="fa fa-check accept" aria-hidden="true" style="font-size: 55px;"></i>
                                                                                    <h4 class="accept" style="margin-top: 0px;">ACCEPTED</h4>
                                                                                @elseif ($tournaments_registration->confirmation->approval->status == 0)
                                                                                    <i class="fa fa-ban reject" aria-hidden="true" style="font-size: 55px;"></i>
                                                                                    <h4 class="reject" style="margin-top: 0px;">REJECTED</h4>
                                                                                @endif
                                                                            @else
                                                                                <i class="fa fa-clock-o pending" aria-hidden="true" style="font-size: 55px;"></i>
                                                                                <h4 class="pending" style="margin-top: 0px;">PENDING</h4>
                                                                            @endif
                                                                        @else
                                                                            <i class="fa fa-exclamation-circle not-confirmed" aria-hidden="true" style="font-size: 55px;"></i>
                                                                            <h6 class="not-confirmed" style="margin-top: 0px;">NOT CONFIRMED</h4>
                                                                        @endif
                                                                    </div>
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
                                    @endforeach
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
                            <div class="tab-pane fade" id="in-progress-tournaments-tab">
                                @if (count($in_progress_tournaments) > 0)
                                    <?php $start = 1; ?>
                                    @foreach ($in_progress_tournaments as $tournaments_registration)
                                        @if ($start % 2 == 1)
                                            <div class="row in-progress-tournaments-rows">
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
                                                            <p>{{ $tournaments_registration->team->name }}</p>
                                                            <p style="color: #afaeae;">{{ date('d F Y', strtotime($tournaments_registration->tournament->start_date)) }} - {{ date('d F Y', strtotime($tournaments_registration->tournament->end_date)) }}</p>
                                                            @if ($tournaments_registration->qr_identifier)
                                                                <a role="button" data-toggle="modal" data-target="#show-barcode-modal" data-src="{{ asset('storage/tournament/qr/'.$tournaments_registration->qr_identifier.'.png') }}" style="position: absolute;right: 5px;bottom: -10px;font-size: 12px;color: #afaeae;">
                                                                    <img src="{{ asset('storage/tournament/qr/'.$tournaments_registration->qr_identifier.'.png') }}" style="width: 50px;height: 50px;">
                                                                </a>
                                                            @endif
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
                                            <strong style="font-size: 64px;">No Tournament In Progress</strong>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="tab-pane fade" id="completed-tournaments-tab">
                                @if (count($completed_tournaments) > 0)
                                    <?php $start = 1; ?>
                                    @foreach ($completed_tournaments as $tournaments_registration)
                                        @if ($start % 2 == 1)
                                            <div class="row completed-tournaments-rows">
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
                                                            <p>{{ $tournaments_registration->team->name }}</p>
                                                            <p style="color: #afaeae;">{{ date('d F Y', strtotime($tournaments_registration->tournament->start_date)) }} - {{ date('d F Y', strtotime($tournaments_registration->tournament->end_date)) }}</p>
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
                                            <strong style="font-size: 64px;">No Tournament Completed</strong>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="tab-pane fade" id="cancelled-tournaments-tab">
                                @if (count($cancelled_tournaments) > 0)
                                    <?php $start = 1; ?>
                                    @foreach ($cancelled_tournaments as $tournaments_registration)
                                        @if ($start % 2 == 1)
                                            <div class="row cancelled-tournaments-rows">
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
                                                            <p>{{ $tournaments_registration->team->name }}</p>
                                                            <p style="color: #afaeae;">{{ date('d F Y', strtotime($tournaments_registration->tournament->start_date)) }} - {{ date('d F Y', strtotime($tournaments_registration->tournament->end_date)) }}</p>
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
                                            <strong style="font-size: 64px;">No Tournament Cancelled</strong>
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

    <!-- Profile Picture Modal -->
    <div class="modal modal-remove-padding-right" id="profile-picture-modal" tabindex="-1" role="dialog" aria-labelledby="profile-picture-modal-label">
        <div class="modal-dialog modal-dialog-fixed-width-320" role="document">
            <div class="modal-content modal-content-custom">
                <div class="modal-header modal-header-border-bottom-custom">
                    <h1 class="modal-title modal-title-align-center" id="profile-picture-modal-label">Profile Picture</h1>
                </div>
                <div class="modal-body">
                    <div class="alert alert-success" role="alert" style="margin-left: 5px;margin-right: 5px;display: none;">
                        <ul id="profile-picture-alert-container">
                            <!-- All message that want to deliver to the Participant -->
                        </ul>
                    </div>
                    <form id="form-profile-picture" class="form-padding-left-right-15 text-center" enctype="multipart/form-data">
                        <label>Upload Profile Picture</label>
                        <div class="form-group form-group-margin-bottom-0">
                            <div class="fileinput fileinput-new" data-provides="fileinput">
                                <div class="fileinput-new thumbnail" style="width: 150px; height: 150px;">
                                    @if ($participant->picture_file_name)
                                        <img id="default-profile-picture-modal" src="{{ asset('storage/member/'.$participant->picture_file_name) }}" style="width: 140px;height: 140px;">
                                    @else
                                        <img id="default-profile-picture-modal" src="{{ asset('img/default-profile.jpg') }}" style="width: 140px;height: 140px;">
                                    @endif
                                </div>
                                <div class="fileinput-preview fileinput-exists thumbnail" style="width: 150px; height: 150px;">
                                </div>
                                <div>
                                    <span id="btn-file-size-profile-picture" class="btn btn-default btn-file">
                                        <span class="fileinput-new">Browse</span>
                                        <span class="fileinput-exists">Change</span>
                                        <input type="file" name="profile_picture" accept="image/jpeg, image/png" required="required">
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

    <!-- Settings Modal -->
    <div class="modal modal-remove-padding-right" id="settings-modal" tabindex="-1" role="dialog" aria-labelledby="settings-modal-label">
        <div class="modal-dialog modal-dialog-fixed-width-400" role="document">
            <div class="modal-content modal-content-custom">
                <div class="modal-header modal-header-border-bottom-custom">
                    <h1 class="modal-title modal-title-align-center" id="settings-modal-label">Settings</h1>
                </div>
                <div class="modal-body">
                    <div class="alert alert-success" role="alert" style="margin-left: 5px;margin-right: 5px;display: none;">
                        <ul id="settings-alert-container">
                            <!-- All message that want to deliver to the Participant -->
                        </ul>
                    </div>
                    <form id="form-settings" class="form-padding-left-right-15" enctype="multipart/form-data">
                        <label>Upload Identity Card</label>
                        <div class="form-group form-group-margin-bottom-0">
                            <div class="fileinput fileinput-new" data-provides="fileinput">
                                <div class="fileinput-new thumbnail" style="width: 338px; height: 188px;">
                                    @if ($identification_file_name)
                                        <img id="default-settings-picture-modal" src="{{ asset('storage/member/identification/'.$identification_file_name) }}" style="width: 328px;height: 178px;">
                                    @else
                                        <img id="default-settings-picture-modal" src="{{ asset('img/holder328x178.png') }}" style="width: 328px;height: 178px;">
                                    @endif
                                </div>
                                <div class="fileinput-preview fileinput-exists thumbnail" style="width: 338px; height: 188px;">
                                </div>
                                <div>
                                    <span id="btn-file-size-settings" class="btn btn-default btn-file">
                                        <span class="fileinput-new">Browse</span>
                                        <span class="fileinput-exists">Change</span>
                                        <input type="file" name="identity_card" accept="image/jpeg, image/png" required="required">
                                    </span>
                                    <a href="#" class="btn btn-default btn-custom fileinput-exists" data-dismiss="fileinput" style="width: 168px;float: right;">Remove</a>
                                </div>
                            </div>
                        </div>
                        <div class="form-group text-right form-group-margin-bottom-0">
                            <button type="submit" class="btn btn-default btn-custom ladda-button" data-style="zoom-out" data-spinner-color="#A9A9A9" id="btn-save-form-settings">
                                <span class="ladda-label">Save</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Team Modal -->
    <div class="modal modal-remove-padding-right" id="create-team-modal" tabindex="-1" role="dialog" aria-labelledby="create-team-modal-label">
        <div class="modal-dialog modal-dialog-fixed-width-350" role="document">
            <div class="modal-content modal-content-custom">
                <div class="modal-header modal-header-border-bottom-custom">
                    <h1 class="modal-title modal-title-align-center" id="create-team-modal-label">Create Team</h1>
                </div>
                <div class="modal-body">
                    <div class="alert alert-success" role="alert" style="margin-left: 5px;margin-right: 5px;display: none;">
                        <ul id="create-team-alert-container">
                            <!-- All message that want to deliver to the Participant -->
                        </ul>
                    </div>
                    <form id="form-create-team" class="form-padding-left-right-15" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-xs-4">
                                <div class="fileinput fileinput-new" data-provides="fileinput">
                                    <div class="fileinput-new thumbnail" style="width: 75px; height: 75px;">
                                        <img src="{{ asset('img/default-group.png') }}" style="width: 65px;height: 65px;">
                                    </div>
                                    <div class="fileinput-preview fileinput-exists thumbnail" style="width: 75px; height: 75px;">
                                    </div>
                                    <div>
                                        <span class="btn btn-default btn-file" style="width: 75px;">
                                            <span class="fileinput-new">Browse</span>
                                            <span class="fileinput-exists">Change</span>
                                            <input type="file" name="picture" accept="image/jpeg, image/png">
                                        </span>
                                        <a href="#" class="btn btn-default btn-custom fileinput-exists" data-dismiss="fileinput" style="width: 75px;">Remove</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-8">
                                <input type="text" class="form-control" name="name" placeholder="Team Name" required="required" style="margin-bottom: 7px;">
                                <input type="text" class="form-control" name="join_password" id="txtbox-join-password" placeholder="Team Join Code" required="required" style="margin-bottom: 5px;">
                                <div style="text-align: right;">
                                    <div class="checkbox" style="margin: 0px 10px 0px 0px;display: inline-block;">
                                        <label>
                                            <input type="checkbox" name="with_join_password" id="ckbox-join-password" value="1" checked="checked"> Join Code
                                        </label>
                                    </div>
                                    <button type="submit" class="btn btn-default btn-custom ladda-button" data-style="zoom-out" data-spinner-color="#A9A9A9" id="btn-create-form-create-team" style="display: inline-block;">
                                        <span class="ladda-label">Create</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Show Image Modal -->
    <div class="modal modal-remove-padding-right" id="show-barcode-modal" tabindex="-1" role="dialog" aria-labelledby="show-barcode-modal-label">
        <div class="modal-dialog modal-dialog-fixed-width-200" role="document">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <img id="barcode-img" src="">
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    @include('participant.footer.footer')
@endsection

@section('script')
    <script src="{{ asset('vendor/x-editable/dist/bootstrap3-editable/js/bootstrap-editable.js') }}"></script>
    <script src="{{ asset('vendor/jasny-bootstrap/dist/js/jasny-bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/participant/profile.js') }}"></script>
@endsection
