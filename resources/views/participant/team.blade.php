@extends('participant.main.master')

@section('title', 'Teams')

@section('style')
    <link href="{{ asset('css/participant/footer.css') }}" rel="stylesheet">
    <link href="{{ asset('css/participant/search-input.css') }}" rel="stylesheet">
    <style type="text/css">
        .team-wrapper {
            display: block;
        }
        .team-wrapper:first-child {
            margin-bottom: 10px;
        }
        .team-wrapper+.team-wrapper {
            margin-bottom: 10px;
        }
        .team-wrapper:last-child {
            margin-bottom: 0;
        }
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
                    <input type="text" id="txtbox-search-team" class="form-control" value="{{ $name }}" placeholder="Search name..." >
                </div>
            </div>
        </div>
        @if (count($teams) > 0)
            @foreach ($teams as $key_team => $team)
                {{-- @if (($key_team + 1) % 10 == 1) --}}
                    @if ($key_team + 1 == 1)
                        <div id="team-list-container-{{ ceil(($key_team + 1) / 1) }}" style="width: 700px;margin-left: 15px;margin-top: 20px;">
                    @else
                        <div id="team-list-container-{{ ceil(($key_team + 1) / 1) }}" style="display: none;width: 700px;margin-left: 15px;margin-top: 20px;">
                    @endif
                {{-- @endif --}}
                    <a class="team-wrapper" href="{{ url('/team/'.$team->id) }}">
                        <div class="row well-custom" style="padding: 10px 0px;">
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
                {{-- @if (($key_team + 1) % 10 == 0 || ($key_team + 1) == count($teams)) --}}
                    </div>
                {{-- @endif --}}
            @endforeach
            <nav aria-label="Page navigation" style="text-align: center;">
                <ul id="team-pagination" class="pagination">
                    <li class="disabled">
                        <a href="#previous" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    @for ($page_start = 1; $page_start <= (ceil(count($teams) / 1)); $page_start++)
                        @if ($page_start == 1)
                            <li class="active">
                        @else
                            <li>
                        @endif
                            <a href="#team-list-container-{{ $page_start }}">{{ $page_start }}</a>
                        </li>
                    @endfor
                    @if (ceil(count($teams) / 1) == 1)
                        <li class="disabled">
                    @else
                        <li>
                    @endif
                        <a href="#next" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        @else
            <div class="row">
                <div class="col-xs-12">
                    <div class="text-center" style="opacity: 0.2;padding-top: 100px;">
                        <div>
                            <i class="fa fa-times" aria-hidden="true" style="font-size: 192px;"></i>
                        </div>
                        <strong style="font-size: 64px;">No Team Available</strong>
                    </div>
                </div>
            </div>
        @endif
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
    <script src="{{ asset('js/participant/team-pagination.js') }}"></script>
@endsection
