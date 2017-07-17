@extends('participant.main.master')

@section('title', 'Tournament Confirm Payment')

@section('style')
    <link href="{{ asset('css/participant/footer.css') }}" rel="stylesheet">
    <style type="text/css">
        #tournament-confirm-payment-container {
            min-height: 536px;
        }

        #tournament-confirm-payment-header {
            padding: 0 15px;
        }
        #tournament-confirm-payment-header > div.col-xs-12 {
            border-bottom: 1px solid #fff;
        }
        #tournament-confirm-payment-header h3 {
            margin-top: 0;
        }

        #tournament-confirm-payment-body {
            margin-bottom: 15px;
            margin-top: 15px;
            padding: 0 15px;
        }
        .table-confirm-payment-information > tbody > tr > td {
            border-top: 0;
            padding: 4px;
        }
        .table-confirm-payment-information > tbody > tr > td.vertical-middle {
            vertical-align: middle;
        }
        .table-confirm-payment-information > tbody > tr > td.vertical-middle > p {
            margin: 0;
        }
        .font-bold {
            font-weight: bold;
        }
        .x-small-and-zero-margin {
            font-size: x-small;
            margin: 0;
        }
        #proof-payment {
            height: 175px;
            width: 175px;
        }
    </style>
@endsection

@section('content')
    <div id="tournament-confirm-payment-container" class="container">
        <div id="tournament-confirm-payment-header" class="row">
            <div class="col-xs-12">
                <h3 style="color:#fff">Confirm Payment - {{ $tournament_registration->tournament->name }}</h3>
            </div>
        </div>
        <div id="tournament-confirm-payment-body" class="row">
            <div class="col-xs-8 alert alert-success" role="alert" style="display: none;">
                <ul id="tournament-confirm-payment-alert-container">
                    <!-- All message that want to deliver to the Participant -->
                </ul>
            </div>
            <div class="col-xs-8">
                <form id="form-tournament-confirm-payment">
                    <table class="table table-confirm-payment-information">
                        <tbody>
                            <tr>
                                <td class="font-bold">Registration ID</td>
                                <td>{{ $tournament_registration->id }}</td>
                            </tr>
                            <tr>
                                <td class="font-bold">Team Name</td>
                                <td>{{ $tournament_registration->team->name }}</td>
                            </tr>
                            <tr>
                                <td class="font-bold">Participant Member</td>
                                <td>
                                    @foreach ($tournament_registration->members as $member)
                                        <p>-. {{ $member->name }}</p>
                                    @endforeach
                                </td>
                            </tr>
                            <tr>
                                <td class="font-bold">Total Payment</td>
                                <td>Rp. {{ number_format($tournament_registration->tournament->entry_fee, 0, ',', '.') }}</td>
                            </tr>
                            @if ($tournament_registration->confirmation)
                                <tr>
                                    <td class="vertical-middle font-bold">Transfer Name</td>
                                    <td><input type="text" id="transfer-name" name="name" class="form-control" required="required" value="{{ $tournament_registration->confirmation->name }}"></td>
                                </tr>
                                <tr>
                                    <td class="vertical-middle font-bold">Transfer To</td>
                                    <td>
                                        <select class="form-control" id="transfer-bank" name="bank" required="required">
                                            <option value disabled="disabled" selected="selected">-- Please choose one --</option>
                                            @if ($tournament_registration->confirmation->banks_id == 1)
                                                <option value="1" selected="selected">BCA</option>
                                                <option value="2">BRI</option>
                                            @elseif ($tournament_registration->confirmation->banks_id == 2)
                                                <option value="1">BCA</option>
                                                <option value="2" selected="selected">BRI</option>
                                            @else
                                                <option value="1">BCA</option>
                                                <option value="2">BRI</option>
                                            @endif
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="vertical-middle font-bold">
                                        <p>Upload Proof of Payment</p>
                                        <img id="proof-payment" src="{{ asset('storage/tournament/confirmation/'.$tournament_registration->confirmation->confirmation_file_name) }}">
                                    </td>
                                    <td><input type="file" id="transfer-confirmation-file-name" name="confirmation_file_name" accept="image/jpeg, image/png" required="required"></td>
                                </tr>
                            @else
                                <tr>
                                    <td class="vertical-middle font-bold">Transfer Name</td>
                                    <td><input type="text" id="transfer-name" name="name" class="form-control" required="required"></td>
                                </tr>
                                <tr>
                                    <td class="vertical-middle font-bold">Transfer To</td>
                                    <td>
                                        <select class="form-control" id="transfer-bank" name="bank" required="required">
                                            <option value disabled="disabled" selected="selected">-- Please choose one --</option>
                                            <option value="1">BCA</option>
                                            <option value="2">BRI</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="vertical-middle font-bold">
                                        <p>Upload Proof of Payment</p>
                                        <img id="proof-payment" src="" style="display: none;">
                                    </td>
                                    <td><input type="file" id="transfer-confirmation-file-name" name="confirmation_file_name" class="form-control" accept="image/jpeg, image/png" required="required"></td>
                                </tr>
                            @endif
                            <tr>
                                <td colspan="2"><p class="x-small-and-zero-margin">**Confirmation will be checked in 1x24 hours</p></td> 
                            </tr>
                            <tr>
                                <td class="text-right" colspan="2">
                                    <button type="submit" id="btn-tournament-confirm-payment" class="btn btn-default btn-custom ladda-button" data-style="zoom-out" data-spinner-color="#A9A9A9">
                                        <span class="ladda-label">Confirm</span>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    @include('participant.footer.footer')
@endsection

@section('script')
    <script src="{{ asset('js/participant/tournament-confirm-payment.js') }}"></script>
@endsection
