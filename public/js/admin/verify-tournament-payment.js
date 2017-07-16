$(document).ready(function (e) {
    window.dTablesTournamentPayment = $("#tournament-payment-table").DataTable({
        "order" : [[6, "asc"]]
    });

    $("#tournament-payment-table_paginate").find(".pagination").addClass("pagination-custom");

    $("#show-image-modal").on("shown.bs.modal", function(e) {
        var btn_trigger = $(e.relatedTarget);

        $("#confirmation-tournament-payment-img").attr("src", btn_trigger.data("src"));
    });

    $(".btn-approve-tournament-payment").on("click", function(e) {
        e.preventDefault();

        var tournament_registration_confirmation_id = $(this).data("id");

        swal({
            "title" : "Approve Tournament Payment",
            "text" : "Are you sure want to approve this tournament payment? (Registration ID = " + tournament_registration_confirmation_id + ")",
            "type" : "warning",
            "customClass" : "sweet-alert-custom",
            "showCancelButton" : true,
            "showConfirmButton" : true,
            "confirmButtonText" : "Yes, i want.",
            "cancelButtonText" : "No, i won't.",
            "closeOnConfirm" : false,
            "showLoaderOnConfirm" : true
        }, function() {
            $.ajax({
                "type" : "POST",
                "url" : api_url + "tournament-payment/" + tournament_registration_confirmation_id + "/approve",
                "headers" : {
                    "Accept" : "application/json",
                    "Authorization" : "Bearer " + document.cookie.replace(/(?:(?:^|.*;\s*)admin_token\s*\=\s*([^;]*).*$)|^.*$/, "$1")
                }
            })
                .done(function(data) {
                    if (data.code == 200) {
                        swal({
                            "title" : "Approve Tournament Payment Success",
                            "text" : data.message[0],
                            "type" : "success",
                            "customClass" : "sweet-alert-custom",
                            "showConfirmButton" : false,
                            "timer" : 1000
                        });
                        // dTablesTournamentPayment.row($("#tournament-payment-table-row-" + tournament_registration_confirmation_id)).remove().draw();
                        window.location.reload();
                    } else {
                        var swal_text = "";
                        $.each(data.message, function(index, value) {
                            if (swal_text != "") {
                                swal_text += "\n";
                            }
                            swal_text += value;
                        });

                        swal({
                            "title" : "Approve Tournament Payment Fail",
                            "text" : swal_text,
                            "type" : "error",
                            "customClass" : "sweet-alert-custom"
                        });
                    }
                })
                .fail(function() {
                    swal({
                        "title" : "Approve Tournament Payment Fail",
                        "text" : "Something went wrong. Please try again.",
                        "type" : "error",
                        "customClass" : "sweet-alert-custom"
                    });
                });
        });
    });

    $(".btn-decline-tournament-payment").on("click", function(e) {
        e.preventDefault();

        var tournament_registration_confirmation_id = $(this).data("id");

        swal({
            "title" : "Decline Tournament Payment",
            "text" : "Are you sure want to decline this tournament payment? (Registration ID = " + tournament_registration_confirmation_id + ")",
            "type" : "warning",
            "customClass" : "sweet-alert-custom",
            "showCancelButton" : true,
            "showConfirmButton" : true,
            "confirmButtonText" : "Yes, i want.",
            "cancelButtonText" : "No, i won't.",
            "closeOnConfirm" : false,
            "showLoaderOnConfirm" : true
        }, function() {
            $.ajax({
                "type" : "POST",
                "url" : api_url + "tournament-payment/" + tournament_registration_confirmation_id + "/decline",
                "headers" : {
                    "Accept" : "application/json",
                    "Authorization" : "Bearer " + document.cookie.replace(/(?:(?:^|.*;\s*)admin_token\s*\=\s*([^;]*).*$)|^.*$/, "$1")
                }
            })
                .done(function(data) {
                    if (data.code == 200) {
                        swal({
                            "title" : "Decline Tournament Payment Success",
                            "text" : data.message[0],
                            "type" : "success",
                            "customClass" : "sweet-alert-custom",
                            "showConfirmButton" : false,
                            "timer" : 1000
                        });
                        // dTablesTournamentPayment.row($("#tournament-payment-table-row-" + tournament_registration_confirmation_id)).remove().draw();
                        window.location.reload();
                    } else {
                        var swal_text = "";
                        $.each(data.message, function(index, value) {
                            if (swal_text != "") {
                                swal_text += "\n";
                            }
                            swal_text += value;
                        });

                        swal({
                            "title" : "Decline Tournament Payment Fail",
                            "text" : swal_text,
                            "type" : "error",
                            "customClass" : "sweet-alert-custom"
                        });
                    }
                })
                .fail(function() {
                    swal({
                        "title" : "Decline Tournament Payment Fail",
                        "text" : "Something went wrong. Please try again.",
                        "type" : "error",
                        "customClass" : "sweet-alert-custom"
                    });
                });
        });
    });

    $(".approved-tooltip-info").tooltip({
        "delay" : 250,
        "html" : true,
        "title": function() {
            var action = $(this).data("action");
            var name = $(this).data("name");
            var date = $(this).data("date");

            return "<span class=\"tooltip-action\">" + action + "</span> by <strong class=\"tooltip-name\">" + name + "</strong> at <strong class=\"tooltip-date\">" + date + "</strong>.";
        },
        "trigger" : "click"
    });

    $(".btn-undo-tournament-payment").on("click", function(e) {
        e.preventDefault();

        var tournament_registration_confirmation_id = $(this).data("id");
        var action = $(this).data("action");

        swal({
            "title" : "Undo " + action + " Tournament Payment",
            "text" : "Are you sure want to Undo " + action + " this tournament payment? (Registration ID = " + tournament_registration_confirmation_id + ")",
            "type" : "warning",
            "customClass" : "sweet-alert-custom",
            "showCancelButton" : true,
            "showConfirmButton" : true,
            "confirmButtonText" : "Yes, i want.",
            "cancelButtonText" : "No, i won't.",
            "closeOnConfirm" : false,
            "showLoaderOnConfirm" : true
        }, function() {
            $.ajax({
                "type" : "POST",
                "url" : api_url + "tournament-payment/" + tournament_registration_confirmation_id + "/undo",
                "headers" : {
                    "Accept" : "application/json",
                    "Authorization" : "Bearer " + document.cookie.replace(/(?:(?:^|.*;\s*)admin_token\s*\=\s*([^;]*).*$)|^.*$/, "$1")
                }
            })
                .done(function(data) {
                    if (data.code == 200) {
                        swal({
                            "title" : "Undo " + action + " Tournament Payment Success",
                            "text" : data.message[0],
                            "type" : "success",
                            "customClass" : "sweet-alert-custom",
                            "showConfirmButton" : false,
                            "timer" : 1000
                        });
                        // dTablesTournamentPayment.row($("#tournament-payment-table-row-" + tournament_registration_confirmation_id)).remove().draw();
                        window.location.reload();
                    } else {
                        var swal_text = "";
                        $.each(data.message, function(index, value) {
                            if (swal_text != "") {
                                swal_text += "\n";
                            }
                            swal_text += value;
                        });

                        swal({
                            "title" : "Undo " + action + " Tournament Payment Fail",
                            "text" : swal_text,
                            "type" : "error",
                            "customClass" : "sweet-alert-custom"
                        });
                    }
                })
                .fail(function() {
                    swal({
                        "title" : "Undo " + action + " Tournament Payment Fail",
                        "text" : "Something went wrong. Please try again.",
                        "type" : "error",
                        "customClass" : "sweet-alert-custom"
                    });
                });
        });
    });
});
