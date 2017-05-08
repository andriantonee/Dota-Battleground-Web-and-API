@extends('organizer.main.master')

@section('title', 'Home')

@section('style')
    <link href="{{ asset('css/organizer/sidebar.css') }}" rel="stylesheet">
@endsection

@section('header')
    @include('organizer.navbar.navbar')
@endsection

@section('content')
    <div id="wrapper">
        <div id="page-wrapper">
            <div class="container-fluid">
                <div class="row" style="border-bottom: 1px solid black;">
                    <div class="col-xs-4">
                        <h2 style="margin-top: 0px;">My Tournament</h2>
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
                <div style="margin-top: 25px;margin-bottom: 25px;">
                    <div class="row" style="margin-bottom: 15px;">
                        <div class="col-xs-offset-2 col-xs-8" style="border: 1px solid black;padding: 15px;position: relative;">
                            <h4 style="margin-top: 0px;">Tournament Name</h4>
                            <h6>Created on 05/05/2017</h6>
                            <h6 style="margin-bottom: 0px;">Event Start 31/05/2017</h6>
                            <div class="text-right" style="position: absolute;top: 10px;right: 15px;width: 200px">
                                <span><i class="fa fa-users" aria-hidden="true"></i>&nbsp;&nbsp;5/10</span>
                                <h6 style="margin-top: 40px;">In Progress</h6>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-offset-2 col-xs-8" style="border: 1px solid black;padding: 15px;position: relative;">
                            <h4 style="margin-top: 0px;">Tournament Name</h4>
                            <h6>Created on 05/05/2017</h6>
                            <h6 style="margin-bottom: 0px;">Event Start 31/05/2017</h6>
                            <div class="text-right" style="position: absolute;top: 10px;right: 15px;width: 200px">
                                <span><i class="fa fa-users" aria-hidden="true"></i>&nbsp;&nbsp;10/10</span>
                                <h6 style="margin-top: 40px;">Finished on 30/06/2017</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
@endsection

@section('script')
@endsection
