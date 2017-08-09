@extends('admin.main.master')

@section('title', 'Suspend Member')

@section('style')
    <link href="{{ asset('vendor/datatables.net-bs/css/dataTables.bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/organizer/sidebar.css') }}" rel="stylesheet">
    <link href="{{ asset('css/participant/modify-modal.css') }}" rel="stylesheet">
    <style type="text/css">
        .suspend-member-header {
            border-bottom: 1px solid #e3e3e3;
            color:#fff;
        }
        .suspend-member-header h2 {
            margin-top: 0;
        }
        .suspend-member-body {
            margin-top: 15px;
        }
        .btn-width-25 {
            width: 25px;
        }
    </style>
@endsection

@section('header')
    @include('admin.navbar.navbar')
@endsection

@section('content')
    <div id="wrapper">
        <div id="page-wrapper">
            <div class="container-fluid well well-transparent">
                <div class="row suspend-member-header">
                    <div class="col-xs-12">
                        <h2>Suspend Member</h2>
                    </div>
                </div>
                <div class="row suspend-member-body">
                    <div class="col-xs-12">
                        <table id="member-table" class="table table-schedule table-bordered table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>Member ID</th>
                                    <th>Name</th>
                                    <th>E-mail</th>
                                    <th>Member Type</th>
                                    <th>Status</th>
                                    <th>Register At</th>
                                    <th style="width: 35px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($members as $member)
                                    <tr class="member-table-row" id="member-table-row-{{ $member->id }}">
                                        <td>{{ $member->id }}</td>
                                        <td>{{ $member->name }}</td>
                                        <td>{{ $member->email }}</td>
                                        <td>
                                            @if ($member->member_type == 1)
                                                Participant
                                            @elseif ($member->member_type == 2)
                                                Organizer
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($member->banned == 0)
                                                <span class="label label-success">Not Banned</span>
                                            @elseif ($member->banned == 1)
                                                <span class="label label-danger">Banned</span>
                                            @endif
                                        </td>
                                        <td>{{ $member->created_at->format('d F Y H:i:s') }}</td>
                                        <td class="text-center">
                                            @if ($member->banned == 0)
                                                <button class="btn btn-default btn-xs btn-danger btn-width-25 btn-ban-member" data-id="{{ $member->id }}"><i class="fa fa-ban"></i></button>
                                            @elseif ($member->banned == 1)
                                                <button class="btn btn-default btn-xs btn-success btn-width-25 btn-activate-member" data-id="{{ $member->id }}"><i class="fa fa-user"></i></button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
@endsection

@section('script')
    <script src="{{ asset('vendor/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/admin/suspend-member.js') }}"></script>
@endsection
