@extends('participant.main.master')

@section('title', 'Teams')

@section('style')
    <link href="{{ asset('css/participant/footer.css') }}" rel="stylesheet">
    <link href="{{ asset('css/participant/search-input.css') }}" rel="stylesheet">
    <style type="text/css">

    </style>
@endsection

@section('content')
    <div class="container" style="min-height: 536px;">
        <div class="row" style="border-bottom: 1px solid #cecece;">
            <div class="col-xs-6">
                <h1 style="margin-top: 0px;color:#fff">Teams</h1>
            </div>
            <div class="col-xs-6">
                <div class="input-group stylish-input-group">
                    <span class="input-group-addon">
                        <i class="glyphicon glyphicon-search"></i>
                    </span>
                    <input type="text" id="txtbox-search-team" class="form-control" placeholder="Search name..." >
                </div>
            </div>
        </div>
        <div id="team-list-container" style="width: 700px;margin-left: 15px;margin-top: 20px;">
            @foreach ($teams as $team)
                <a href="{{ url('/team/'.$team->id) }}">
                    <div class="row well-custom" style="margin-bottom: 5px;padding: 10px 0px;">
                        <div class="col-xs-2">
                            <div class="thumbnail" style="height: 80px;width: 80px;margin: 0px auto;">
                                @if ($team->picture_file_name)
                                    <img src="{{ asset('storage/team/'.$team->picture_file_name) }}" style="height: 70px;width: 70px;">
                                @else
                                    <img src="{{ asset('img/default-group.png') }}" style="height: 70px;width: 70px;">
                                @endif
                            </div>
                        </div>
                        <div class="col-xs-5">
                            <h3 style="margin-top: 13px;color: #f45138;">{{ $team->name }}</h3>
                            <p style="color: #afaeae;"><span class="team-count" >{{ $team->details_count }}</span> Member</p>
                        </div>
                        <div class="col-xs-5 text-right" style="padding-top: 20px;">
                            @if (count($team->details) == 0 && $participant)
                                @if (count($team->invitation_list) > 0)
                                    <button class="btn btn-default btn-custom btn-accept accept-invite-request" style="font-size: 20px;" data-team-id="{{ $team->id }}" data-team-name="{{ $team->name }}" data-refresh="false">
                                        <i class="glyphicon glyphicon-ok" style="color:#68ff90"></i>&nbsp;&nbsp;Accept
                                    </button>
                                    @if ($team->join_password)
                                        <button class="btn btn-default btn-custom btn-reject reject-invite-request" style="font-size: 20px;" data-team-id="{{ $team->id }}" data-team-name="{{ $team->name }}" data-refresh="false" data-with-password="true">
                                            <i class="glyphicon glyphicon-remove" style="color:#fc5d5d"></i>&nbsp;&nbsp;Reject
                                        </button>
                                    @else
                                        <button class="btn btn-default btn-custom btn-reject reject-invite-request" style="font-size: 20px;" data-team-id="{{ $team->id }}" data-team-name="{{ $team->name }}" data-refresh="false" data-with-password="false">
                                            <i class="glyphicon glyphicon-remove" style="color:#fc5d5d"></i>&nbsp;&nbsp;Reject
                                        </button>
                                    @endif
                                @else
                                    @if ($team->join_password)
                                        <button class="btn btn-default btn-custom join-with-password" style="font-size: 20px;" data-team-id="{{ $team->id }}" data-team-name="{{ $team->name }}" data-refresh="false">
                                            <i class="glyphicon glyphicon-log-in" style="color:#fff841"></i>&nbsp;&nbsp;Join Team
                                        </button>
                                        
                                    @else
                                        <button class="btn btn-default btn-custom join-without-password" style="font-size: 20px;" data-team-id="{{ $team->id }}" data-team-name="{{ $team->name }}" data-refresh="false">
                                            <i class="glyphicon glyphicon-log-in"></i>&nbsp;&nbsp;Join Team
                                        </button>
                                    @endif
                                @endif
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
@endsection

@section('footer')
    @include('participant.footer.footer')
@endsection

@section('script')
    @if ($participant)
        <script src="{{ asset('js/participant/team.js') }}"></script>
    @endif
    <script src="{{ asset('js/participant/search-team.js') }}"></script>
@endsection
