@extends('admin.main.master')

@section('title', 'Verify Tournament')

@section('style')
    <link href="{{ asset('vendor/datatables.net-bs/css/dataTables.bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/organizer/sidebar.css') }}" rel="stylesheet">
    <link href="{{ asset('css/participant/modify-modal.css') }}" rel="stylesheet">
    <style type="text/css">
        .verify-tournament-payment-header {
            border-bottom: 1px solid #e3e3e3;
            color:#fff;
        }
        .verify-tournament-payment-header h2 {
            margin-top: 0;
        }
        .verify-tournament-payment-body {
            margin-top: 15px;
        }
        #confirmation-tournament-payment-img {
            height: 500px;
            width: 100%;
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
                <div class="row verify-tournament-payment-header">
                    <div class="col-xs-12">
                        <h2>Verify Tournament Payment</h2>
                    </div>
                </div>
                <div class="row verify-tournament-payment-body">
                    <div class="col-xs-12">
                        <table id="tournament-payment-table" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tournament Name</th>
                                    <th>Total Payment</th>
                                    <th>Transfer Name</th>
                                    <th>Transfer Bank</th>
                                    <th>First Confirmed Date</th>
                                    <th>Last Confirmed Date</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tournament_registration_confirmations as $tournament_registration_confirmation)
                                    <tr class="payment-tournament-table-row" id="tournament-payment-table-row-{{ $tournament_registration_confirmation->tournaments_registrations_id }}">
                                        <td>{{ $tournament_registration_confirmation->tournaments_registrations_id }}</td>
                                        <td>{{ $tournament_registration_confirmation->registration->tournament->name }}</td>
                                        <td>Rp. {{ number_format($tournament_registration_confirmation->registration->tournament->entry_fee, 0, ',', '.') }}</td>
                                        <td>{{ $tournament_registration_confirmation->name }}</td>
                                        <td>{{ $tournament_registration_confirmation->banks_id == 1 ? 'BCA' : ($tournament_registration_confirmation->banks_id == 2 ? 'BRI' : 'Unknown') }}</td>
                                        <td>{{ $tournament_registration_confirmation->created_at->format('d F Y H:i:s') }}</td>
                                        <td>{{ $tournament_registration_confirmation->updated_at->format('d F Y H:i:s') }}</td>
                                        <td class="text-center">
                                            <button class="btn btn-default btn-xs btn-info btn-width-25" data-src="{{ asset('storage/tournament/confirmation/'.$tournament_registration_confirmation->confirmation_file_name) }}" data-toggle="modal" data-target="#show-image-modal"><i class="fa fa-picture-o"></i></button>
                                            <button class="btn btn-default btn-xs btn-success btn-width-25 btn-approve-tournament-payment" data-id="{{ $tournament_registration_confirmation->tournaments_registrations_id }}"><i class="fa fa-check"></i></button>
                                            <button class="btn btn-default btn-xs btn-danger btn-width-25 btn-decline-tournament-payment" data-id="{{ $tournament_registration_confirmation->tournaments_registrations_id }}"><i class="fa fa-times"></i></button>
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
                    <img id="confirmation-tournament-payment-img" src="">
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
    <script src="{{ asset('js/admin/verify-tournament-payment.js') }}"></script>
@endsection
