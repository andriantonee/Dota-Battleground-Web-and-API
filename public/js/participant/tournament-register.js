$(document).ready(function() {
    $(".list-group-team-item").on("click", function(e) {
        var that = $(this);
        var team_id = that.data("team-id");

        $.ajax({
                "type" : "GET",
                "url" : api_url + "team/" + team_id + "/member",
                "headers" : {
                    "Accept" : "application/json",
                    "Authorization" : "Bearer " + document.cookie.replace(/(?:(?:^|.*;\s*)participant_token\s*\=\s*([^;]*).*$)|^.*$/, "$1")
                },
                "data" : {
                    "tournament_id" : location.pathname.split("/")[2]
                },
                "beforeSend" : function() {
                    swal({
                        "title" : "Fetching Member...",
                        "text" : "This will close automatically when it is done.",
                        "showConfirmButton" : false
                    });
                }
            })
                .done(function(data) {
                    if (data.code == 200) {
                        $(".list-group-team-item.selected").removeClass("selected");
                        that.addClass("selected");

                        var members_HTML = "";
                        $.each(data.members, function(index, value) {
                            members_HTML = members_HTML + 
                                "<div class=\"list-group-member-item\">" +
                                    "<div class=\"list-group-member-item-img\">" +
                                        "<img src=\"" + value.picture_file_name + "\">" +
                                    "</div>" +
                                    "<div class=\"list-group-member-item-detail\">" +
                                        "<p><label class=\"members-label\" for=\"ckbox-member-" + value.id + "\">" + value.name + "</label></p>" +
                                    "</div>" +
                                    "<div class=\"list-group-member-item-checkbox\">" +
                                        "<input type=\"checkbox\" id=\"ckbox-member-" + value.id + "\" name=\"members[]\" value=\"" + value.id + "\">" +
                                    "</div>" +
                                "</div>";
                        });
                        $("#list-member-container").html(members_HTML);
                        $("#player-left").html("5");

                        $("#team").val(team_id);

                        swal.close();
                    } else {
                        var swal_text = "";
                        $.each(data.message, function(index, value) {
                            if (swal_text != "") {
                                swal_text += "\n";
                            }
                            swal_text += value;
                        });

                        swal({
                            "title" : "Fetching Member Fail",
                            "text" : swal_text,
                            "type" : "error"
                        });
                    }
                })
                .fail(function() {
                    swal({
                        "title" : "Fetching Member Fail",
                        "text" : "Something went wrong. Please try again.",
                        "type" : "error"
                    });
                });
    });

    $(document).on("click", "input[name=\"members[]\"]", function(e) {
        if ($("input[name=\"members[]\"]:checked").length == max) {
            $("input[name=\"members[]\"]:not(:checked)").prop("disabled", true);
            $("#btn-tournament-register").prop("disabled", false);
        } else {
            $("input[name=\"members[]\"]:disabled").prop("disabled", false);
            $("#btn-tournament-register").prop("disabled", true);
        }
        $("#player-left").html(max - $("input[name=\"members[]\"]:checked").length);
    });

    $("#form-tournament-register").on("submit", function(e) {
        e.preventDefault();

        var data = $(this).serialize();
        var tournament_name = $(this).data("tournament-name");

        swal({
            "title" : "Join Tournament",
            "text" : "Are you sure want to join \"" + tournament_name + "\" tournament?",
            "type" : "warning",
            "showCancelButton" : true,
            "showConfirmButton" : true,
            "confirmButtonText" : "Yes, i want.",
            "cancelButtonText" : "No, i won't.",
            "closeOnConfirm" : false,
            "showLoaderOnConfirm" : true
        }, function() {
            $.ajax({
                "type" : "POST",
                "url" : api_url + "tournament/" + location.pathname.split("/")[2] + "/register",
                "data" : data,
                "headers" : {
                    "Accept" : "application/json",
                    "Authorization" : "Bearer " + document.cookie.replace(/(?:(?:^|.*;\s*)participant_token\s*\=\s*([^;]*).*$)|^.*$/, "$1")
                }
            })
                .done(function(data) {
                    if (data.code == 201) {
                        swal({
                            "title" : "Join Tournament Success",
                            "text" : data.message[0],
                            "type" : "success",
                            "showConfirmButton" : false,
                            "timer" : 1000
                        }, function() {
                            window.location.href = data.url;
                        });
                    } else {
                        var swal_text = "";
                        $.each(data.message, function(index, value) {
                            if (swal_text != "") {
                                swal_text += "\n";
                            }
                            swal_text += value;
                        });

                        swal({
                            "title" : "Join Tournament Fail",
                            "text" : swal_text,
                            "type" : "error"
                        });
                    }
                })
                .fail(function() {
                    swal({
                        "title" : "Join Tournament Fail",
                        "text" : "Something went wrong. Please try again.",
                        "type" : "error"
                    });
                });
        });
    });
});
