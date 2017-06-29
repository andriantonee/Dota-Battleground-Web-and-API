@extends('organizer.main.master')

@section('title', 'Home')

@section('style')
    <link href="{{ asset('css/organizer/sidebar.css') }}" rel="stylesheet">
    <style type="text/css">
        .in-progress{
            color:#20bc36;/*green*/
        }
        a.tournament-link{
            height: 130px;
            padding: 15px;
            position: relative;
        }
        a.tournament-link:first-child {
            margin-bottom: 15px;
        }
        a.tournament-link:last-child {
            margin-bottom: 0 !important;
        }
        a.tournament-link+a.tournament-link {
            margin-bottom: 15px;
        }
        .tournament-image{
            display: inline-block;
            vertical-align: top;
            height: 100px;
            width: 100px;
        }
        .tournament-image > img{
            width: 100px;
            height: 100px;
        }
        .tournament-detail{
            display: inline-block;
            margin-left: 15px;
            margin-top: 0;
            width: 470px; 
        }
        .tournament-detail-1{
            margin-top:4px;
        }
        .tournament-detail-1 > h6{
            margin-top: 1px;
            margin-left: 15px;
            margin-bottom: 0px;
            color:#9d9d9d
        }
        .tournament-detail-1 > h6 > div{
            display: inline-block;
            width: 150px;
        }
    </style>
@endsection

@section('header')
    @include('organizer.navbar.navbar')
@endsection

@section('content')
    <div id="wrapper">
        <div id="page-wrapper">
            <div class="container-fluid well well-transparent">
                <div class="row" style="border-bottom: 1px solid #e3e3e3;">
                    <div class="col-xs-4">
                        <h2 style="margin-top: 0px;color:#fff">My Tournament</h2>
                    </div>
                    <div class="col-xs-8 text-right" style="padding-top: 7px;">
                        <label class="radio-inline">
                            <input type="radio" name="tournament_filter" id="tournament_filter_all" value="1"> All
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tournament_filter" id="tournament_filter_upcoming" value="2"> Upcoming
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tournament_filter" id="tournament_filter_in_progress" value="3"> In Progress
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tournament_filter" id="tournament_filter_completed" value="4"> Completed
                        </label>
                    </div>
                </div>
                <div class="row" style="margin-top: 25px;margin-bottom: 25px;">
                    @if (count($tournaments) > 0)
                        <div class="col-xs-offset-2 col-xs-8">
                            @foreach ($tournaments as $tournament)
                                <a href="{{ url('/organizer/tournament/'.$tournament->id.'/detail') }}" class="tournament-link well-custom">
                                    <div class="tournament-image">
                                        <img src="{{ asset('storage/tournament/'.$tournament->logo_file_name) }}" >
                                    </div>
                                    <div class="tournament-detail">
                                        <h3 style="margin-top: 0px;margin-bottom: 0px;">{{ $tournament->name }}</h3>
                                        <div class="tournament-detail-1">
                                            <h6>
                                            <div>Tournament Type</div>: {{ $tournament->type }}
                                            </h6>
                                            <h6>
                                                <div>Entry Fee</div>: Rp. {{ number_format($tournament->entry_fee, 0, ',', '.') }}
                                            </h6>
                                            <h6>
                                                <div>Registration Closed Date</div>: {{ date('d F Y H:i', strtotime($tournament->registration_closed)) }}
                                            </h6>
                                            <h6>
                                                <div>Event Date</div>: {{ date('d F Y', strtotime($tournament->start_date)) }} - {{ date('d F Y', strtotime($tournament->end_date)) }}
                                            </h6>
                                            <h6>
                                                <div>Created Date</div>: {{ $tournament->created_at->format('d F Y H:i:s') }}
                                            </h6>
                                        </div>
                                        
                                    </div>
                                    <div class="text-right" style="position: absolute;top: 10px;right: 15px;width: 200px;">
                                        <p><i class="fa fa-users" aria-hidden="true"></i>&nbsp;&nbsp;{{ $tournament->registrations_count }}/{{ $tournament->max_participant }}</p>
                                        @if (date('Y-m-d H:i:s') <= $tournament->registration_closed)
                                            <h6 class="in-progress" style="margin-top: 73px;">Upcoming</h6>
                                        @else
                                            @if ($tournament->start == 0)
                                                <h6 class="in-progress" style="margin-top: 73px;">Upcoming</h6>
                                            @else
                                                @if ($tournament->complete == 0)
                                                    <h6 class="in-progress" style="margin-top: 73px;">In Progress</h6>
                                                @else
                                                    <h6 class="in-progress" style="margin-top: 73px;">Complete</h6>
                                                @endif
                                            @endif
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="col-xs-12 text-center" style="opacity: 0.2;">
                            <div>
                                <i class="fa fa-times" aria-hidden="true" style="font-size: 192px;"></i>
                            </div>
                            <strong style="font-size: 64px;">No Data Available</strong>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
@endsection

@section('script')
@endsection
