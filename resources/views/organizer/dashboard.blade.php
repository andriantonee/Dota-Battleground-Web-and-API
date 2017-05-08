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
                    <div class="col-xs-12">
                        <h2 style="margin-top: 0px;">Dashboard</h2>
                    </div>
                </div>
                <div style="margin-top: 25px;margin-bottom: 25px;">
                    <div class="row" style="margin-bottom: 15px;">
                        <div class="col-xs-offset-2 col-xs-8" style="border: 1px solid black;padding: 15px;position: relative;">
                            <h4 style="margin-top: 0px;">Tournament Name</h4>
                            <h6>Created on 05/05/2017</h6>
                            <h6 style="margin-bottom: 0px;">Event Start 31/05/2017</h6>
                            <div class="text-center" style="position: absolute;top: 10px;right: 25px;width: 100px">
                                <i class="fa fa-clock-o" aria-hidden="true" style="font-size: 55px;"></i>
                                <h4 style="margin-top: 0px;color: blue;">PENDING</h4>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-offset-2 col-xs-8" style="border: 1px solid black;padding: 15px;position: relative;">
                            <h4 style="margin-top: 0px;">Tournament Name</h4>
                            <h6>Created on 05/05/2017</h6>
                            <h6 style="margin-bottom: 0px;">Event Start 31/05/2017</h6>
                            <div class="text-center" style="position: absolute;top: 10px;right: 25px;width: 100px">
                                <i class="fa fa-ban" aria-hidden="true" style="font-size: 55px;"></i>
                                <h4 style="margin-top: 0px;color: red;">REJECTED</h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12 text-center">
                        <a href="{{ url('/organizer/tournament/create') }}" role="button" class="btn btn-default">
                            Create New Tournament
                        </a>
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
