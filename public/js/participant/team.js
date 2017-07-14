$(document).on("click", ".join-with-password", function(e) {
    e.preventDefault();

    var that = $(this);
    var team_id = $(this).data("team-id");
    var team_name = $(this).data("team-name");
    var refresh = $(this).data("refresh");

    swal({
        "title" : "Join team \"" + team_name + "\"",
        "text" : "Enter Join Code",
        "type" : "input",
        "customClass" : "sweet-alert-custom",
        "showCancelButton" : true,
        "showConfirmButton" : true,
        "confirmButtonText" : "Join",
        "cancelButtonText" : "Cancel",
        "closeOnConfirm" : false,
        "animation" : "slide-from-top",
        "inputPlaceholder" : "Join Code",
        "showLoaderOnConfirm" : true
    }, function(join_password) {
        if (join_password === false) {
            return false;
        }
        if (join_password === "") {
            swal.showInputError("Join Code must not empty.");
            return false;
        }

        $.ajax({
            "type" : "POST",
            "url" : api_url + "team/" + team_id + "/join",
            "headers" : {
                "Accept" : "application/json",
                "Authorization" : "Bearer " + document.cookie.replace(/(?:(?:^|.*;\s*)participant_token\s*\=\s*([^;]*).*$)|^.*$/, "$1")
            },
            "data" : {
                "join_password" : join_password
            }
        })
            .done(function(data) {
                if (data.code == 200) {
                    swal({
                        "title" : "Join Team Success",
                        "text" : data.message[0],
                        "type" : "success",
                        "customClass" : "sweet-alert-custom",
                        "showConfirmButton" : false,
                        "timer" : 1000
                    });

                    // if (refresh == true) {
                    //     location.reload();
                    // } else {
                    //     that.parent().parent().find(".team-count").html(data.count);
                    //     that.parent().empty();
                    // }
                    location.reload();
                } else {
                    var swal_text = "";
                    $.each(data.message, function(index, value) {
                        if (swal_text != "") {
                            swal_text += "\n";
                        }
                        swal_text += value;
                    });

                    swal({
                        "title" : "Join Team Fail",
                        "text" : swal_text,
                        "type" : "error",
                        "customClass" : "sweet-alert-custom"
                    });
                }
            })
            .fail(function() {
                swal({
                    "title" : "Join Team Fail",
                    "text" : "Something went wrong.\n Please try again.",
                    "type" : "error",
                    "customClass" : "sweet-alert-custom"
                });
            });
    });
});

$(document).on("click", ".join-without-password", function(e) {
    e.preventDefault();

    var that = $(this);
    var team_id = $(this).data("team-id");
    var team_name = $(this).data("team-name");
    var refresh = $(this).data("refresh");

    swal({
        "title" : "Join team \"" + team_name + "\"",
        "text" : "Do you want to join the team?",
        "type" : "info",
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
            "url" : api_url + "team/" + team_id + "/join",
            "headers" : {
                "Accept" : "application/json",
                "Authorization" : "Bearer " + document.cookie.replace(/(?:(?:^|.*;\s*)participant_token\s*\=\s*([^;]*).*$)|^.*$/, "$1")
            }
        })
            .done(function(data) {
                if (data.code == 200) {
                    swal({
                        "title" : "Join Team Success",
                        "text" : data.message[0],
                        "type" : "success",
                        "customClass" : "sweet-alert-custom",
                        "showConfirmButton" : false,
                        "timer" : 1000
                    });

                    // if (refresh == true) {
                    //     location.reload();
                    // } else {
                    //     that.parent().parent().find(".team-count").html(data.count);
                    //     that.parent().remove();
                    // }
                    location.reload();
                } else {
                    var swal_text = "";
                    $.each(data.message, function(index, value) {
                        if (swal_text != "") {
                            swal_text += "\n";
                        }
                        swal_text += value;
                    });

                    swal({
                        "title" : "Join Team Fail",
                        "text" : swal_text,
                        "type" : "error",
                        "customClass" : "sweet-alert-custom"
                    });
                }
            })
            .fail(function() {
                swal({
                    "title" : "Join Team Fail",
                    "text" : "Something went wrong. Please try again.",
                    "type" : "error",
                    "customClass" : "sweet-alert-custom"
                });
            });
    });
});

$(document).on("click", ".accept-invite-request", function(e) {
    e.preventDefault();

    var that = $(this);
    var team_id = $(this).data("team-id");
    var team_name = $(this).data("team-name");
    var refresh = $(this).data("refresh");

    swal({
        "title" : "Accept invitation from team \"" + team_name + "\"",
        "text" : "Do you want to accept this invitation?",
        "type" : "info",
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
            "url" : api_url + "team/" + team_id + "/accept-invitation",
            "headers" : {
                "Accept" : "application/json",
                "Authorization" : "Bearer " + document.cookie.replace(/(?:(?:^|.*;\s*)participant_token\s*\=\s*([^;]*).*$)|^.*$/, "$1")
            }
        })
            .done(function(data) {
                if (data.code == 200) {
                    swal({
                        "title" : "Accept Team Invitation Success",
                        "text" : data.message[0],
                        "type" : "success",
                        "customClass" : "sweet-alert-custom",
                        "showConfirmButton" : false,
                        "timer" : 1000
                    });

                    // if (refresh == true) {
                    //     location.reload();
                    // } else {
                    //     that.parent().parent().find(".team-count").html(data.count);
                    //     that.parent().remove();
                    // }
                    location.reload();
                } else {
                    var swal_text = "";
                    $.each(data.message, function(index, value) {
                        if (swal_text != "") {
                            swal_text += "\n";
                        }
                        swal_text += value;
                    });

                    swal({
                        "title" : "Accept Team Invitation Fail",
                        "text" : swal_text,
                        "type" : "error",
                        "customClass" : "sweet-alert-custom"
                    });
                }
            })
            .fail(function() {
                swal({
                    "title" : "Accept Team Invitation Fail",
                    "text" : "Something went wrong. Please try again.",
                    "type" : "error",
                    "customClass" : "sweet-alert-custom"
                });
            });
    });
});

$(document).on("click", ".reject-invite-request", function(e) {
    e.preventDefault();

    var that = $(this);
    var team_id = $(this).data("team-id");
    var team_name = $(this).data("team-name");
    var refresh = $(this).data("refresh");
    var with_password = $(this).data("with-password");

    swal({
        "title" : "Reject invitation from team \"" + team_name + "\"",
        "text" : "Do you want to reject this invitation?",
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
            "url" : api_url + "team/" + team_id + "/reject-invitation",
            "headers" : {
                "Accept" : "application/json",
                "Authorization" : "Bearer " + document.cookie.replace(/(?:(?:^|.*;\s*)participant_token\s*\=\s*([^;]*).*$)|^.*$/, "$1")
            }
        })
            .done(function(data) {
                if (data.code == 200) {
                    swal({
                        "title" : "Reject Team Invitation Success",
                        "text" : data.message[0],
                        "type" : "success",
                        "customClass" : "sweet-alert-custom",
                        "showConfirmButton" : false,
                        "timer" : 1000
                    });

                    // var join_html = "";
                    // if (with_password == true) {
                    //     join_html = join_html + 
                    //         "<button class=\"btn btn-default join-with-password\" style=\"font-size: 20px;\" data-team-id=\"" + team_id + "\" data-team-name=\"" + team_name + "\" data-refresh=\"" + refresh + "\">" +
                    //             "<i class=\"glyphicon glyphicon-log-in\"></i>&nbsp;&nbsp;Join Team" +
                    //         "</button>";
                    // } else {
                    //     join_html = join_html + 
                    //         "<button class=\"btn btn-default join-without-password\" style=\"font-size: 20px;\" data-team-id=\"" + team_id + "\" data-team-name=\"" + team_name + "\" data-refresh=\"" + refresh + "\">" +
                    //             "<i class=\"glyphicon glyphicon-log-in\"></i>&nbsp;&nbsp;Join Team" +
                    //         "</button>";
                    // }
                    // that.parent().html(join_html);
                    location.reload();
                } else {
                    var swal_text = "";
                    $.each(data.message, function(index, value) {
                        if (swal_text != "") {
                            swal_text += "\n";
                        }
                        swal_text += value;
                    });

                    swal({
                        "title" : "Reject Team Invitation Fail",
                        "text" : swal_text,
                        "type" : "error",
                        "customClass" : "sweet-alert-custom"
                    });
                }
            })
            .fail(function() {
                swal({
                    "title" : "Reject Team Invitation Fail",
                    "text" : "Something went wrong. Please try again.",
                    "type" : "error",
                    "customClass" : "sweet-alert-custom"
                });
            });
    });
});
