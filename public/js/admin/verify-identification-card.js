$(document).ready(function (e) {
    window.dTablesIdentificationCard = $("#identification-card-table").DataTable({
        "order" : [[3, "asc"]],
        "drawCallback" : function(settings) {
            $("#identification-card-table_paginate").find(".pagination").addClass("pagination-custom");
        }
    });

    $("#show-image-modal").on("shown.bs.modal", function(e) {
        var btn_trigger = $(e.relatedTarget);

        $("#identification-card-img").attr("src", btn_trigger.data("src"));
    });

    $(document).on("click", ".btn-approve-identification-card", function(e) {
        e.preventDefault();

        var identification_card_id = $(this).data("id");

        swal({
            "title" : "Approve Identification Card",
            "text" : "Are you sure want to approve this identification card? (Identification Card ID = " + identification_card_id + ")",
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
                "url" : api_url + "identification-card/" + identification_card_id + "/approve",
                "headers" : {
                    "Accept" : "application/json",
                    "Authorization" : "Bearer " + document.cookie.replace(/(?:(?:^|.*;\s*)admin_token\s*\=\s*([^;]*).*$)|^.*$/, "$1")
                }
            })
                .done(function(data) {
                    if (data.code == 200) {
                        swal({
                            "title" : "Approve Identification Card Success",
                            "text" : data.message[0],
                            "type" : "success",
                            "customClass" : "sweet-alert-custom",
                            "showConfirmButton" : false,
                            "timer" : 1000
                        });
                        // dTablesIdentificationCard.row($("#identification-card-table-row-" + identification_card_id)).remove().draw();
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
                            "title" : "Approve Identification Card Fail",
                            "text" : swal_text,
                            "type" : "error",
                            "customClass" : "sweet-alert-custom"
                        });
                    }
                })
                .fail(function() {
                    swal({
                        "title" : "Approve Identification Card Fail",
                        "text" : "Something went wrong. Please try again.",
                        "type" : "error",
                        "customClass" : "sweet-alert-custom"
                    });
                });
        });
    });

    $(document).on("click", ".btn-decline-identification-card", function(e) {
        e.preventDefault();

        var identification_card_id = $(this).data("id");

        swal({
            "title" : "Decline Identification Card",
            "text" : "Are you sure want to decline this Identification Card? (Identification Card ID = " + identification_card_id + ")",
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
                "url" : api_url + "identification-card/" + identification_card_id + "/decline",
                "headers" : {
                    "Accept" : "application/json",
                    "Authorization" : "Bearer " + document.cookie.replace(/(?:(?:^|.*;\s*)admin_token\s*\=\s*([^;]*).*$)|^.*$/, "$1")
                }
            })
                .done(function(data) {
                    if (data.code == 200) {
                        swal({
                            "title" : "Decline Identification Card Success",
                            "text" : data.message[0],
                            "type" : "success",
                            "customClass" : "sweet-alert-custom",
                            "showConfirmButton" : false,
                            "timer" : 1000
                        });
                        // dTablesIdentificationCard.row($("#identification-card-table-row-" + identification_card_id)).remove().draw();
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
                            "title" : "Decline Identification Card Fail",
                            "text" : swal_text,
                            "type" : "error",
                            "customClass" : "sweet-alert-custom"
                        });
                    }
                })
                .fail(function() {
                    swal({
                        "title" : "Decline Identification Card Fail",
                        "text" : "Something went wrong. Please try again.",
                        "type" : "error",
                        "customClass" : "sweet-alert-custom"
                    });
                });
        });
    });
});
