@extends('admin.main.master')

@section('title', 'Verify Identification Card')

@section('style')
    <link href="{{ asset('vendor/datatables.net-bs/css/dataTables.bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/organizer/sidebar.css') }}" rel="stylesheet">
    <link href="{{ asset('css/participant/modify-modal.css') }}" rel="stylesheet">
    <style type="text/css">
        .verify-identification-card-header {
            border-bottom: 1px solid #e3e3e3;
            color:#fff;
        }
        .verify-identification-card-header h2 {
            margin-top: 0;
        }
        .verify-identification-card-body {
            margin-top: 15px;
        }
        #identification-card-img {
            height: 178px;
            width: 328px;
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
                <div class="row verify-identification-card-header">
                    <div class="col-xs-12">
                        <h2>Verify Identification Card</h2>
                    </div>
                </div>
                <div class="row verify-identification-card-body">
                    <div class="col-xs-12">
                        <table id="identification-card-table" class="table table-schedule table-bordered table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>Identification Card ID</th>
                                    <th>Name</th>
                                    <th>E-mail</th>
                                    <th>Uploaded At</th>
                                    <th style="width: 70px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($identifications as $identification)
                                    <tr class="identification-card-table-row" id="identification-card-table-row-{{ $identification->id }}">
                                        <td>{{ $identification->id }}</td>
                                        <td>{{ $identification->member->name }}</td>
                                        <td>{{ $identification->member->email }}</td>
                                        <td>{{ $identification->created_at->format('d F Y H:i:s') }}</td>
                                        <td class="text-center">
                                            <button class="btn btn-default btn-xs btn-info btn-width-25" data-src="{{ asset('storage/member/identification/'.$identification->identification_file_name) }}" data-toggle="modal" data-target="#show-image-modal"><i class="fa fa-picture-o"></i></button>
                                            <button class="btn btn-default btn-xs btn-success btn-width-25 btn-approve-identification-card" data-id="{{ $identification->id }}"><i class="fa fa-check"></i></button>
                                            <button class="btn btn-default btn-xs btn-danger btn-width-25 btn-decline-identification-card" data-id="{{ $identification->id }}"><i class="fa fa-times"></i></button>
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

    <!-- Show Image Modal -->
    <div class="modal modal-remove-padding-right" id="show-image-modal" tabindex="-1" role="dialog" aria-labelledby="show-image-modal-label">
        <div class="modal-dialog modal-dialog-fixed-width-500" role="document">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <img id="identification-card-img" src="">
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
    <script src="{{ asset('js/admin/verify-identification-card.js') }}"></script>
@endsection
