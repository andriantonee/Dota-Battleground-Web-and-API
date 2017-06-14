$(document).ready(function(e) {
    $("#schedule-date-and-time-datetimepicker").datetimepicker({
        "format" : "DD/MM/YYYY HH:mm:ss",
        "sideBySide" : true
    });

	$("#form-tournament-settings").on("submit", function(e) {
        e.preventDefault();

        var data = $(this).serialize();
        var btn_update = Ladda.create(document.querySelector("#btn-tournament-settings"));

        $.ajax({
            "type" : "PUT",
            "url" : api_url + "tournament/" + location.pathname.split("/")[3],
            "headers" : {
                "Accept" : "application/json",
                "Authorization" : "Bearer " + document.cookie.replace(/(?:(?:^|.*;\s*)organizer_token\s*\=\s*([^;]*).*$)|^.*$/, "$1")
            },
            "data" : data,
            "beforeSend" : function() {
                $("#tournament-settings-alert-container").parent().hide();
                $("#tournament-settings-alert-container").empty();
                btn_update.start();
            }
        })
            .done(function(data) {
                var li_message = "";
                $.each(data.message, function(index, value) {
                    li_message += "<li>" + value + "</li>";
                });
                if (data.code == 200) {
                    $("#tournament-settings-alert-container").parent().removeClass("alert-success alert-danger").addClass("alert-success");
                } else {
                    $("#tournament-settings-alert-container").parent().removeClass("alert-success alert-danger").addClass("alert-danger");
                }
                $("#tournament-settings-alert-container").parent().show();
                $("#tournament-settings-alert-container").append(li_message);

                $('html, body').animate({
                    scrollTop: $("#tournament-settings-alert-container").parent().offset().top
                }, 1000);
            })
            .fail(function() {
                $("#tournament-settings-alert-container").parent().removeClass("alert-success alert-danger").addClass("alert-danger");
                $("#tournament-settings-alert-container").parent().show();
                $("#tournament-settings-alert-container").append("<li>Something went wrong. Please try again.</li>");

                $('html, body').animate({
                    scrollTop: $("#tournament-settings-alert-container").parent().offset().top
                }, 1000);
            })
            .always(function() {
                btn_update.stop();
            });
    });

    $("#form-tournament-brackets").on("submit", function(e) {
        e.preventDefault();

        var data = $(this).serialize();
        var btn_update = Ladda.create(document.querySelector("#btn-tournament-brackets"));

        $.ajax({
            "type" : "PUT",
            "url" : api_url + "tournament/" + location.pathname.split("/")[3] + "/type",
            "headers" : {
                "Accept" : "application/json",
                "Authorization" : "Bearer " + document.cookie.replace(/(?:(?:^|.*;\s*)organizer_token\s*\=\s*([^;]*).*$)|^.*$/, "$1")
            },
            "data" : data,
            "beforeSend" : function() {
                $("#tournament-brackets-alert-container").parent().hide();
                $("#tournament-brackets-alert-container").empty();
                btn_update.start();
            }
        })
            .done(function(data) {
                var li_message = "";
                $.each(data.message, function(index, value) {
                    li_message += "<li>" + value + "</li>";
                });
                if (data.code == 200) {
                    $("#tournament-brackets-alert-container").parent().removeClass("alert-success alert-danger").addClass("alert-success");
                } else {
                    $("#tournament-brackets-alert-container").parent().removeClass("alert-success alert-danger").addClass("alert-danger");
                }
                $("#tournament-brackets-alert-container").parent().show();
                $("#tournament-brackets-alert-container").append(li_message);

                $('html, body').animate({
                    scrollTop: $("#tournament-brackets-alert-container").parent().offset().top
                }, 1000);

                if (data.code == 200) {
                    $("#tournament-brackets-iframe").attr("src", $("#tournament-brackets-iframe").attr("src"));
                }
            })
            .fail(function() {
                $("#tournament-brackets-alert-container").parent().removeClass("alert-success alert-danger").addClass("alert-danger");
                $("#tournament-brackets-alert-container").parent().show();
                $("#tournament-brackets-alert-container").append("<li>Something went wrong. Please try again.</li>");

                $('html, body').animate({
                    scrollTop: $("#tournament-brackets-alert-container").parent().offset().top
                }, 1000);
            })
            .always(function() {
                btn_update.stop();
            });
    });

    $("#btn-tournament-start").on("click", function(e) {
        e.preventDefault();

        swal({
            "title" : "Start Tournament",
            "text" : "Do you want to start it?",
            "type" : "warning",
            "showCancelButton" : true,
            "showConfirmButton" : true,
            "confirmButtonText" : "Yes, start it.",
            "cancelButtonText" : "No, dont't start it.",
            "closeOnConfirm" : false,
            "showLoaderOnConfirm" : true
        }, function() {
            $.ajax({
                "type" : "PUT",
                "url" : api_url + "tournament/" + location.pathname.split("/")[3] + "/start",
                "headers" : {
                    "Accept" : "application/json",
                    "Authorization" : "Bearer " + document.cookie.replace(/(?:(?:^|.*;\s*)organizer_token\s*\=\s*([^;]*).*$)|^.*$/, "$1")
                }
            })
                .done(function(data) {
                    if (data.code == 200) {
                        swal({
                            "title" : "Start Tournament Success",
                            "text" : data.message[0],
                            "type" : "success",
                            "showConfirmButton" : false,
                            "timer" : 1000
                        });

                        location.reload();
                    } else {
                        var html_error_message = "";
                        $.each(data.message, function(index, value) {
                            if (html_error_message != "") {
                                html_error_message += "\n";
                            }

                            html_error_message += value;
                        });

                        swal({
                            "title" : "Start Tournament Fail",
                            "text" : html_error_message,
                            "type" : "error"
                        });
                    }
                })
                .fail(function() {
                    swal({
                        "title" : "Start Tournament Fail",
                        "text" : "Something went wrong. Please try again.",
                        "type" : "error"
                    });
                });
        });
    });

    $("#btn-tournament-end").on("click", function(e) {
        e.preventDefault();

        swal({
            "title" : "End Tournament",
            "text" : "Do you want to end it?",
            "type" : "warning",
            "showCancelButton" : true,
            "showConfirmButton" : true,
            "confirmButtonText" : "Yes, end it.",
            "cancelButtonText" : "No, dont't end it.",
            "closeOnConfirm" : false,
            "showLoaderOnConfirm" : true
        }, function() {
            $.ajax({
                "type" : "PUT",
                "url" : api_url + "tournament/" + location.pathname.split("/")[3] + "/end",
                "headers" : {
                    "Accept" : "application/json",
                    "Authorization" : "Bearer " + document.cookie.replace(/(?:(?:^|.*;\s*)organizer_token\s*\=\s*([^;]*).*$)|^.*$/, "$1")
                }
            })
                .done(function(data) {
                    if (data.code == 200) {
                        swal({
                            "title" : "End Tournament Success",
                            "text" : data.message[0],
                            "type" : "success",
                            "showConfirmButton" : false,
                            "timer" : 1000
                        });

                        location.reload();
                    } else {
                        var html_error_message = "";
                        $.each(data.message, function(index, value) {
                            if (html_error_message != "") {
                                html_error_message += "\n";
                            }

                            html_error_message += value;
                        });

                        swal({
                            "title" : "End Tournament Fail",
                            "text" : html_error_message,
                            "type" : "error"
                        });
                    }
                })
                .fail(function() {
                    swal({
                        "title" : "End Tournament Fail",
                        "text" : "Something went wrong. Please try again.",
                        "type" : "error"
                    });
                });
        });
    });

    $("#schedule-modal").on("show.bs.modal", function(e) {
        var btn_trigger = $(e.relatedTarget);
        var tr_child = btn_trigger.parent().parent().children();
        var tr_child_length = tr_child.length;

        var match_id = btn_trigger.data("match-id");
        var round_id = btn_trigger.data("round-id");
        var round = $("#round-" + round_id + "-title").html().trim();
        var match = tr_child.eq(tr_child_length - 6).html().trim();
        var player_1 = tr_child.eq(tr_child_length - 5).html().trim();
        var player_2 = tr_child.eq(tr_child_length - 3).html().trim();
        var scheduled_time = moment(tr_child.eq(tr_child_length - 2).html().trim(), "dddd, DD MMMM YYYY HH:mm:ss");
        if (scheduled_time.isValid()) {
            scheduled_time = scheduled_time.format("DD/MM/YYYY HH:mm:ss");
        } else {
            scheduled_time = "";
        }

        $("#schedule-round-match-title").html(round + " - Match " + match);
        $("#schedule-versus-title").html(player_1 + " VS " + player_2);
        $("#schedule-date-and-time").val(scheduled_time).trigger("change");
        $("#btn-schedule").data("match-id", match_id);

        $("#schedule-alert-container").parent().hide();
        $("#schedule-alert-container").empty();
    });

    $("#form-schedule").on("submit", function(e) {
        e.preventDefault();

        var match_id = $("#btn-schedule").data("match-id");
        var data = $(this).serialize();
        var btn_update = Ladda.create(document.querySelector("#btn-schedule"));

        $.ajax({
            "type" : "PUT",
            "url" : api_url + "match/" + match_id + "/schedule",
            "headers" : {
                "Accept" : "application/json",
                "Authorization" : "Bearer " + document.cookie.replace(/(?:(?:^|.*;\s*)organizer_token\s*\=\s*([^;]*).*$)|^.*$/, "$1")
            },
            "data" : data,
            "beforeSend" : function() {
                $("#schedule-alert-container").parent().hide();
                $("#schedule-alert-container").empty();
                btn_update.start();
            }
        })
            .done(function(data) {
                var li_message = "";
                $.each(data.message, function(index, value) {
                    li_message += "<li>" + value + "</li>";
                });
                if (data.code == 200) {
                    $("#schedule-alert-container").parent().removeClass("alert-success alert-danger").addClass("alert-success");
                } else {
                    $("#schedule-alert-container").parent().removeClass("alert-success alert-danger").addClass("alert-danger");
                }
                $("#schedule-alert-container").parent().show();
                $("#schedule-alert-container").append(li_message);

                if (data.code == 200) {
                    var child_length = $("button.btn-edit-schedule[data-match-id=\"" + match_id + "\"]").parent().parent().children().length;
                    $("button.btn-edit-schedule[data-match-id=\"" + match_id + "\"]").parent().parent().children().eq(child_length - 2).html(moment($("#schedule-date-and-time").val(), "DD/MM/YYYY HH:mm:ss").format("dddd, DD MMMM YYYY HH:mm:ss"));
                }
            })
            .fail(function() {
                $("#schedule-alert-container").parent().removeClass("alert-success alert-danger").addClass("alert-danger");
                $("#schedule-alert-container").parent().show();
                $("#schedule-alert-container").append("<li>Something went wrong. Please try again.</li>");
            })
            .always(function() {
                btn_update.stop();
            });
    });

    $("#report-match-modal").on("show.bs.modal", function(e) {
        var btn_trigger = $(e.relatedTarget);
        var tr_child = btn_trigger.parent().parent().children();
        var tr_child_length = tr_child.length;

        var match_id = btn_trigger.data("match-id");
        var round_id = btn_trigger.data("round-id");
        var round = $("#round-" + round_id + "-title").html().trim();
        var match = tr_child.eq(tr_child_length - 5).html().trim();
        var player_1 = tr_child.eq(tr_child_length - 4).html().trim();
        var player_2 = tr_child.eq(tr_child_length - 2).html().trim();

        $("#report-match-round-match-title").html(round + " - Match " + match);
        $("#report-match-versus-title").html(player_1 + " VS " + player_2);
        $("#btn-submit-report-match").data("match-id", match_id);
        $("#side-1").html(player_1);
        $("#side-1-score").val(0);
        $("#side-2").html(player_2);
        $("#side-2-score").val(0);
        $("#ckbox-final-score").prop("checked", false);

        $("#report-match-alert-container").parent().hide();
        $("#report-match-alert-container").empty();
    });

    $("#form-report-match").on("submit", function(e) {
        e.preventDefault();

        var match_id = $("#btn-submit-report-match").data("match-id");
        var data = $(this).serialize();
        var btn_submit = Ladda.create(document.querySelector("#btn-submit-report-match"));

        $.ajax({
            "type" : "PUT",
            "url" : api_url + "match/" + match_id + "/score",
            "headers" : {
                "Accept" : "application/json",
                "Authorization" : "Bearer " + document.cookie.replace(/(?:(?:^|.*;\s*)organizer_token\s*\=\s*([^;]*).*$)|^.*$/, "$1")
            },
            "data" : data,
            "beforeSend" : function() {
                $("#report-match-alert-container").parent().hide();
                $("#report-match-alert-container").empty();
                btn_submit.start();
            }
        })
            .done(function(data) {
                var li_message = "";
                $.each(data.message, function(index, value) {
                    li_message += "<li>" + value + "</li>";
                });
                if (data.code == 200) {
                    $("#report-match-alert-container").parent().removeClass("alert-success alert-danger").addClass("alert-success");
                } else {
                    $("#report-match-alert-container").parent().removeClass("alert-success alert-danger").addClass("alert-danger");
                }
                $("#report-match-alert-container").parent().show();
                $("#report-match-alert-container").append(li_message);

                if (data.code == 200) {
                    var child_length = $("button.btn-edit-schedule[data-match-id=\"" + match_id + "\"]").parent().parent().children().length;
                    $("button.btn-edit-schedule[data-match-id=\"" + match_id + "\"]").parent().parent().children().eq(child_length - 2).html(moment($("#schedule-date-and-time").val(), "DD/MM/YYYY HH:mm:ss").format("dddd, DD MMMM YYYY HH:mm:ss"));
                }
            })
            .fail(function() {
                $("#report-match-alert-container").parent().removeClass("alert-success alert-danger").addClass("alert-danger");
                $("#report-match-alert-container").parent().show();
                $("#report-match-alert-container").append("<li>Something went wrong. Please try again.</li>");
            })
            .always(function() {
                btn_submit.stop();
            });
    });
});
