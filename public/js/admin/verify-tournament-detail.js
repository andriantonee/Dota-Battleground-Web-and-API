$(document).ready(function (e) {
    $("#start-date-datetimepicker").datetimepicker({
        "format" : "DD/MM/YYYY",
        "sideBySide" : true,
        "minDate" : minStartDateSchedule
    })
        .on("dp.change", function(e) {
            minEndDateSchedule = e.date;
            $("#end-date-datetimepicker").datetimepicker("destroy");
            $("#end-date-datetimepicker").datetimepicker({
                "format" : "DD/MM/YYYY",
                "sideBySide" : true,
                "minDate" : minEndDateSchedule
            });
        });
    $("#end-date-datetimepicker").datetimepicker({
        "format" : "DD/MM/YYYY",
        "sideBySide" : true,
        "minDate" : minEndDateSchedule
    });

    $("#form-tournament-update").on("submit", function(e) {
        e.preventDefault();

        var data = $(this).serialize();
        var btn_update = Ladda.create(document.querySelector("#btn-update-tournament"));

        $.ajax({
            "type" : "POST",
            "url" : api_url + "tournament/" + location.pathname.split("/")[3] + "/update",
            "headers" : {
                "Accept" : "application/json",
                "Authorization" : "Bearer " + document.cookie.replace(/(?:(?:^|.*;\s*)admin_token\s*\=\s*([^;]*).*$)|^.*$/, "$1")
            },
            "data" : data,
            "beforeSend" : function() {
                $("#tournament-update-alert-container").parent().hide();
                $("#tournament-update-alert-container").empty();
                btn_update.start();
            }
        })
            .done(function(data) {
                var li_message = "";
                $.each(data.message, function(index, value) {
                    li_message += "<li>" + value + "</li>";
                });
                if (data.code == 200) {
                    $("#tournament-update-alert-container").parent().removeClass("alert-success alert-danger").addClass("alert-success");
                } else {
                    $("#tournament-update-alert-container").parent().removeClass("alert-success alert-danger").addClass("alert-danger");
                }
                $("#tournament-update-alert-container").parent().show();
                $("#tournament-update-alert-container").append(li_message);

                $('html, body').animate({
                    scrollTop: $("#tournament-update-alert-container").parent().offset().top
                }, 1000);
            })
            .fail(function() {
                $("#tournament-update-alert-container").parent().removeClass("alert-success alert-danger").addClass("alert-danger");
                $("#tournament-update-alert-container").parent().show();
                $("#tournament-update-alert-container").append("<li>Something went wrong. Please try again.</li>");

                $('html, body').animate({
                    scrollTop: $("#tournament-update-alert-container").parent().offset().top
                }, 1000);
            })
            .always(function() {
                btn_update.stop();
            });
    });

    $("#btn-approve-tournament").on("click", function(e) {
        e.preventDefault();

        var that = $(this);
        var tournament_name = $(this).data("tournament-name");

        swal({
            "title" : "Approve Tournament",
            "text" : "Do you really want to approve \"" + tournament_name + "\"",
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
                "url" : api_url + "tournament/" + location.pathname.split("/")[3] + "/approve",
                "headers" : {
                    "Accept" : "application/json",
                    "Authorization" : "Bearer " + document.cookie.replace(/(?:(?:^|.*;\s*)admin_token\s*\=\s*([^;]*).*$)|^.*$/, "$1")
                }
            })
                .done(function(data) {
                    if (data.code == 200) {
                        // that.parent().parent().remove();

                        swal({
                            "title" : "Approve Tournament Success",
                            "text" : data.message[0],
                            "type" : "success",
                            "customClass" : "sweet-alert-custom",
                            "showConfirmButton" : false,
                            "timer" : 1000
                        });

                        // window.location.replace(location.origin + "/admin");
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
                            "title" : "Approve Tournament Fail",
                            "text" : swal_text,
                            "type" : "error",
                            "customClass" : "sweet-alert-custom"
                        });
                    }
                })
                .fail(function() {
                    swal({
                        "title" : "Approve Tournament Fail",
                        "text" : "Something went wrong. Please try again.",
                        "type" : "error",
                        "customClass" : "sweet-alert-custom"
                    });
                });
        });
    });

    $("#btn-decline-tournament").on("click", function(e) {
        e.preventDefault();

        var that = $(this);
        var tournament_name = $(this).data("tournament-name");

        swal({
            "title" : "Decline Tournament",
            "text" : "Do you really want to decline \"" + tournament_name + "\"",
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
                "url" : api_url + "tournament/" + location.pathname.split("/")[3] + "/decline",
                "headers" : {
                    "Accept" : "application/json",
                    "Authorization" : "Bearer " + document.cookie.replace(/(?:(?:^|.*;\s*)admin_token\s*\=\s*([^;]*).*$)|^.*$/, "$1")
                }
            })
                .done(function(data) {
                    if (data.code == 200) {
                        // that.parent().parent().remove();

                        swal({
                            "title" : "Decline Tournament Success",
                            "text" : data.message[0],
                            "type" : "success",
                            "customClass" : "sweet-alert-custom",
                            "showConfirmButton" : false,
                            "timer" : 1000
                        });

                        // window.location.replace(location.origin + "/admin");
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
                            "title" : "Decline Tournament Fail",
                            "text" : swal_text,
                            "type" : "error",
                            "customClass" : "sweet-alert-custom"
                        });
                    }
                })
                .fail(function() {
                    swal({
                        "title" : "Decline Tournament Fail",
                        "text" : "Something went wrong. Please try again.",
                        "type" : "error",
                        "customClass" : "sweet-alert-custom"
                    });
                });
        });
    });

    $("#btn-undo-tournament").on("click", function(e) {
        e.preventDefault();

        var that = $(this);
        var action = $(this).data("action");
        var tournament_name = $(this).data("tournament-name");

        swal({
            "title" : "Undo " + action + " Tournament",
            "text" : "Do you really want to Undo " + action + " \"" + tournament_name + "\"",
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
                "url" : api_url + "tournament/" + location.pathname.split("/")[3] + "/undo",
                "headers" : {
                    "Accept" : "application/json",
                    "Authorization" : "Bearer " + document.cookie.replace(/(?:(?:^|.*;\s*)admin_token\s*\=\s*([^;]*).*$)|^.*$/, "$1")
                }
            })
                .done(function(data) {
                    if (data.code == 200) {
                        // that.parent().parent().remove();

                        swal({
                            "title" : "Undo " + action + " Tournament Success",
                            "text" : data.message[0],
                            "type" : "success",
                            "customClass" : "sweet-alert-custom",
                            "showConfirmButton" : false,
                            "timer" : 1000
                        });

                        // window.location.replace(location.origin + "/admin");
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
                            "title" : "Undo " + action + " Tournament Fail",
                            "text" : swal_text,
                            "type" : "error",
                            "customClass" : "sweet-alert-custom"
                        });
                    }
                })
                .fail(function() {
                    swal({
                        "title" : "Undo " + action + " Tournament Fail",
                        "text" : "Something went wrong. Please try again.",
                        "type" : "error",
                        "customClass" : "sweet-alert-custom"
                    });
                });
        });
    });
});
