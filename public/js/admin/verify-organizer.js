$(document).ready(function (e) {
    window.dTablesOrganizer = $("#organizer-table").DataTable({
        "order" : [[3, "asc"]],
        "drawCallback" : function(settings) {
            $("#organizer-table_paginate").find(".pagination").addClass("pagination-custom");
        }
    });

    $(document).on("click", ".btn-approve-organizer", function(e) {
        e.preventDefault();

        var organizer_id = $(this).data("id");

        swal({
            "title" : "Approve Organizer",
            "text" : "Are you sure want to approve this organizer? (Organizer ID = " + organizer_id + ")",
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
                "url" : api_url + "organizer/" + organizer_id + "/approve",
                "headers" : {
                    "Accept" : "application/json",
                    "Authorization" : "Bearer " + document.cookie.replace(/(?:(?:^|.*;\s*)admin_token\s*\=\s*([^;]*).*$)|^.*$/, "$1")
                }
            })
                .done(function(data) {
                    if (data.code == 200) {
                        swal({
                            "title" : "Approve Organizer Success",
                            "text" : data.message[0],
                            "type" : "success",
                            "customClass" : "sweet-alert-custom",
                            "showConfirmButton" : false,
                            "timer" : 1000
                        });
                        // dTablesOrganizer.row($("#organizer-table-row-" + organizer_id)).remove().draw();
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
                            "title" : "Approve Organizer Fail",
                            "text" : swal_text,
                            "type" : "error",
                            "customClass" : "sweet-alert-custom"
                        });
                    }
                })
                .fail(function() {
                    swal({
                        "title" : "Approve Organizer Fail",
                        "text" : "Something went wrong. Please try again.",
                        "type" : "error",
                        "customClass" : "sweet-alert-custom"
                    });
                });
        });
    });

    $(document).on("click", ".btn-decline-organizer", function(e) {
        e.preventDefault();

        var organizer_id = $(this).data("id");

        swal({
            "title" : "Decline Organizer",
            "text" : "Are you sure want to decline this organizer? (Organizer ID = " + organizer_id + ")",
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
                "url" : api_url + "organizer/" + organizer_id + "/decline",
                "headers" : {
                    "Accept" : "application/json",
                    "Authorization" : "Bearer " + document.cookie.replace(/(?:(?:^|.*;\s*)admin_token\s*\=\s*([^;]*).*$)|^.*$/, "$1")
                }
            })
                .done(function(data) {
                    if (data.code == 200) {
                        swal({
                            "title" : "Decline Organizer Success",
                            "text" : data.message[0],
                            "type" : "success",
                            "customClass" : "sweet-alert-custom",
                            "showConfirmButton" : false,
                            "timer" : 1000
                        });
                        // dTablesOrganizer.row($("#organizer-table-row-" + organizer_id)).remove().draw();
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
                            "title" : "Decline Organizer Fail",
                            "text" : swal_text,
                            "type" : "error",
                            "customClass" : "sweet-alert-custom"
                        });
                    }
                })
                .fail(function() {
                    swal({
                        "title" : "Decline Organizer Fail",
                        "text" : "Something went wrong. Please try again.",
                        "type" : "error",
                        "customClass" : "sweet-alert-custom"
                    });
                });
        });
    });
});
