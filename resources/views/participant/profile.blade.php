@extends('participant.main.master')

@section('title', 'Profile')

@section('style')
    <link href="{{ asset('vendor/x-editable/dist/bootstrap3-editable/css/bootstrap-editable.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/jasny-bootstrap/dist/css/jasny-bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/participant/footer.css') }}" rel="stylesheet">
    <link href="{{ asset('css/participant/tab-pages.css') }}" rel="stylesheet">
    <link href="{{ asset('css/participant/team.css') }}" rel="stylesheet">
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
                        <span id="editable-email-value">{{ $participant->email }}</span>&nbsp;&nbsp;
                        <a role="button" id="editable-email" class="editable-bottom-border-none">
                            <i class="glyphicon glyphicon-pencil"></i>
                        </a>
                    </h4>
                    <h6>
                        <span id="editable-steam32_id-value">{{ $participant->steam32_id ?: '-' }}</span>&nbsp;&nbsp;
                        <a role="button" id="editable-steam32_id" class="editable-bottom-border-none">
                            <i class="glyphicon glyphicon-pencil"></i>
                        </a>
                    </h6>
                    <a role="button" class="btn btn-default" data-toggle="modal" data-target="#settings-modal" style="position: absolute;right: 0;top: 30px;">
                        <i class="glyphicon glyphicon-cog"></i>&nbsp;&nbsp;Settings
                    </a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="well well-lg" style="background-color: #ffffff;border: 1px solid #000000;border-radius: 0px;">
                <div class="panel with-nav-tabs panel-default" style="border: none;">
                    <div class="panel-heading" style="background-color: transparent;border-color: #000000;">
                        <ul class="nav nav-tabs">
                            <li class="active"><a href="#teams-tab" data-toggle="tab">Teams</a></li>
                            <li><a href="#schedule-tab" data-toggle="tab">Schedule</a></li>
                            <li><a href="#registration-status-tab" data-toggle="tab">Registration Status</a></li>
                            <li><a href="#my-tournaments-tab" data-toggle="tab">My Tournaments</a></li>
                        </ul>
                    </div>
                    <div class="panel-body" style="border: 1px solid #000000;border-top: none;">
                        <div class="tab-content">
                            <div class="tab-pane fade in active" id="teams-tab">
                                <div id="team-list-container">
                                    @foreach ($teams as $team)
                                        <a class="team-list-content" href="{{ url('/team/'.$team->id) }}">
                                            <div class="row" style="padding: 15px 5px;border: 1px solid #000000;margin: 0px;margin-bottom: 15px;">
                                                <div class="col-xs-2">
                                                    <div class="thumbnail" style="margin: 0px auto;width: 75px;height: 75px;">
                                                        <img src="{{ asset($team->picture_file_name ? '/storage/team/'.$team->picture_file_name : 'img/default-group.png') }}" style="width: 65px;height: 65px;">
                                                    </div>
                                                </div>
                                                <div class="col-xs-10">
                                                    <h3 style="margin-top: 12px;">{{ $team->name }}</h3>
                                                    <h5>{{ $team->details_count }} Member</h5>
                                                </div>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                                <div style="margin: 25px 0px;text-align: center;">
                                    <a role="button" class="btn btn-default" data-toggle="modal" data-target="#create-team-modal" style="border-radius: 0px;border-color: #000000;">
                                        <div>
                                            <i class="glyphicon glyphicon-plus-sign" style="font-size: 24px;"></i>
                                        </div>
                                        <span style="width: 100%;text-align: center;">Create New Team</span>
                                    </a>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="schedule-tab">
                                Default 2
                            </div>
                            <div class="tab-pane fade" id="registration-status-tab">
                                Default 3
                            </div>
                            <div class="tab-pane fade" id="my-tournaments-tab">
                                Default 4
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
            <div class="modal-content">
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
                                    <a href="#" class="btn btn-default fileinput-exists" data-dismiss="fileinput" style="width: 74px;float: right;">Remove</a>
                                </div>
                            </div>
                        </div>
                        <div class="form-group form-group-margin-bottom-0">
                            <button type="submit" class="btn btn-default ladda-button" data-style="zoom-out" data-spinner-color="#A9A9A9" id="btn-save-form-profile-picture">
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
            <div class="modal-content">
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
                                    <a href="#" class="btn btn-default fileinput-exists" data-dismiss="fileinput" style="width: 168px;float: right;">Remove</a>
                                </div>
                            </div>
                        </div>
                        <div class="form-group text-right form-group-margin-bottom-0">
                            <button type="submit" class="btn btn-default ladda-button" data-style="zoom-out" data-spinner-color="#A9A9A9" id="btn-save-form-settings">
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
            <div class="modal-content">
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
                                        <a href="#" class="btn btn-default fileinput-exists" data-dismiss="fileinput" style="width: 75px;">Remove</a>
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
                                    <button type="submit" class="btn btn-default ladda-button" data-style="zoom-out" data-spinner-color="#A9A9A9" id="btn-create-form-create-team" style="display: inline-block;">
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
@endsection

@section('footer')
    @include('participant.footer.footer')
@endsection

@section('script')
    <script src="{{ asset('vendor/x-editable/dist/bootstrap3-editable/js/bootstrap-editable.js') }}"></script>
    <script src="{{ asset('vendor/jasny-bootstrap/dist/js/jasny-bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/participant/profile.js') }}"></script>
@endsection
