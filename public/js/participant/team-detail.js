$(document).ready(function() {
    $("#editable-team-name").editable({
        "ajaxOptions" : {
            "type" : "PUT",
            "headers" : {
                "Accept" : "application/json",
                "Authorization" : "Bearer " + document.cookie.replace(/(?:(?:^|.*;\s*)participant_token\s*\=\s*([^;]*).*$)|^.*$/, "$1")
            }
        },
        "autotext" : "never",
        "display" : false,
        "error" : function(response, newValue) {
            if (response.status == 401) {
                window.location.replace("/");
            } else {
                return "Something went wrong. Please try again.";
            }
        },
        "highlight" : false,
        "params" : function(params) {
            var new_params = {
                "name" : params.value
            };
            return new_params;
        },
        "placement" : "bottom",
        "success" : function(response, newValue) {
            if (response.code == 200) {
                $("#editable-team-name-value").text($("#" + $(this).attr("aria-describedby")).find("input[type=\"text\"]").val());

                return true;   
            } else {
                var html_error_message = "";
                $.each(response.message, function(index, value) {
                    if (html_error_message != "") {
                        html_error_message += "\n";
                    }

                    html_error_message += value;
                });
                return html_error_message;
            }
        },
        "type" : "text",
        "url" : api_url + "team/" + location.pathname.split("/")[2],
        "send" : "always",
        "title" : "Enter Team Name",
        "value" : function() {
            return $("#editable-team-name-value").text();
        }
    });

    $("#ckbox-join-password").on("change", function() {
        if ($(this).prop("checked")) {
            $("#txtbox-join-password").prop("disabled", false);
        } else {
            $("#txtbox-join-password").val("");
            $("#txtbox-join-password").prop("disabled", true);
        }
    });

    $("#form-team-settings").on("submit", function(e) {
        e.preventDefault();

        var data = $(this).serialize();
        if (data == "") {
            data = "with_join_password=0";
        }
        var btn_save = Ladda.create(document.querySelector("#btn-save-form-team-settings"));

        $.ajax({
            "type" : "PUT",
            "url" : api_url + "team/" + location.pathname.split("/")[2],
            "data" : data,
            "headers" : {
                "Accept" : "application/json",
                "Authorization" : "Bearer " + document.cookie.replace(/(?:(?:^|.*;\s*)participant_token\s*\=\s*([^;]*).*$)|^.*$/, "$1")
            },
            "beforeSend" : function() {
                $("#team-settings-alert-container").parent().hide();
                $("#team-settings-alert-container").empty();
                btn_save.start();
            }
        })
            .done(function(data) {
                var li_message = "";
                $.each(data.message, function(index, value) {
                    li_message += "<li>" + value + "</li>";
                });
                if (data.code == 200) {
                    $("#team-settings-alert-container").parent().removeClass("alert-success alert-danger").addClass("alert-success");
                } else {
                    $("#team-settings-alert-container").parent().removeClass("alert-success alert-danger").addClass("alert-danger");
                }
                $("#team-settings-alert-container").parent().show();
                $("#team-settings-alert-container").append(li_message);
            })
            .fail(function() {
                $("#team-settings-alert-container").parent().removeClass("alert-success alert-danger").addClass("alert-danger");
                $("#team-settings-alert-container").parent().show();
                $("#team-settings-alert-container").append("<li>Something went wrong. Please try again.</li>");
            })
            .always(function() {
                btn_save.stop();
            });
    });

    $("#form-profile-picture").on("submit", function(e) {
        e.preventDefault();

        var form_data = new FormData(this);
        var btn_save = Ladda.create(document.querySelector("#btn-save-form-profile-picture"));

        $.ajax({
            "type" : "POST",
            "url" : api_url + "team/" + location.pathname.split("/")[2] + "/picture",
            "headers" : {
                "Accept" : "application/json",
                "Authorization" : "Bearer " + document.cookie.replace(/(?:(?:^|.*;\s*)participant_token\s*\=\s*([^;]*).*$)|^.*$/, "$1")
            },
            "data" : form_data,
            "contentType" : false,
            "processData" : false,
            "beforeSend" : function() {
                $("#profile-picture-alert-container").parent().hide();
                $("#profile-picture-alert-container").empty();
                btn_save.start();
            }
        })
            .done(function(data) {
                var li_message = "";
                $.each(data.message, function(index, value) {
                    li_message += "<li>" + value + "</li>";
                });
                if (data.code == 200) {
                    $("#profile-picture-alert-container").parent().removeClass("alert-success alert-danger").addClass("alert-success");
                } else {
                    $("#profile-picture-alert-container").parent().removeClass("alert-success alert-danger").addClass("alert-danger");
                }
                $("#profile-picture-alert-container").parent().show();
                $("#profile-picture-alert-container").append(li_message);

                if (data.code == 200) {
                    $("#default-profile-picture").attr("src", data.file_path);
                    $("#default-profile-picture-modal").attr("src", data.file_path);
                }
            })
            .fail(function() {
                $("#profile-picture-alert-container").parent().removeClass("alert-success alert-danger").addClass("alert-danger");
                $("#profile-picture-alert-container").parent().show();
                $("#profile-picture-alert-container").append("<li>Something went wrong. Please try again.</li>");
            })
            .always(function() {
                btn_save.stop();
            });
    });

    $("#profile-picture-action-delete").on("click", function(e) {
        e.preventDefault();

        swal({
            "title" : "Remove Picture",
            "text" : "Do you want to remove it?",
            "type" : "warning",
            "showCancelButton" : true,
            "showConfirmButton" : true,
            "confirmButtonText" : "Yes, remove it.",
            "cancelButtonText" : "No, dont't remove it.",
            "closeOnConfirm" : false,
            "showLoaderOnConfirm" : true
        }, function() {
            $.ajax({
                "type" : "DELETE",
                "url" : api_url + "team/" + location.pathname.split("/")[2] + "/picture",
                "headers" : {
                    "Accept" : "application/json",
                    "Authorization" : "Bearer " + document.cookie.replace(/(?:(?:^|.*;\s*)participant_token\s*\=\s*([^;]*).*$)|^.*$/, "$1")
                }
            })
                .done(function(data) {
                    if (data.code == 200) {
                        $("#default-profile-picture").attr("src", data.file_path);
                        $("#default-profile-picture-modal").attr("src", data.file_path);

                        swal({
                            "title" : "Remove Picture Success",
                            "text" : data.message[0],
                            "type" : "success",
                            "showConfirmButton" : false,
                            "timer" : 1000
                        });
                    } else {
                        swal({
                            "title" : "Remove Picture Fail",
                            "text" : "Something went wrong. Please try again.",
                            "type" : "error"
                        });
                    }
                })
                .fail(function() {
                    swal({
                        "title" : "Remove Picture Fail",
                        "text" : "Something went wrong. Please try again.",
                        "type" : "error"
                    });
                });
        });
    });

    // $("#invite-members-modal").on("shown.bs.modal", function() {
    //     $("#txtbox-invite-members").focus();
    // });

    var invite_members_typing_timer;
    var invite_members_done_typing_interval = 500;

    $("#txtbox-invite-members").on("keyup", function(e) {
        clearTimeout(invite_members_typing_timer);
        invite_members_typing_timer = setTimeout(invite_members_done_typing, invite_members_done_typing_interval)
    });

    $("#txtbox-invite-members").on("keydown", function(e) {
        clearTimeout(invite_members_typing_timer);
    });

    function invite_members_done_typing() {
        if ($("#txtbox-invite-members").val() != "") {
            $.ajax({
                "type" : "GET",
                "url" : api_url + "team/" + location.pathname.split("/")[2] + "/uninvited-member",
                "headers" : {
                    "Accept" : "application/json",
                    "Authorization" : "Bearer " + document.cookie.replace(/(?:(?:^|.*;\s*)participant_token\s*\=\s*([^;]*).*$)|^.*$/, "$1")
                },
                "data" : {
                    "search_keyword" : $("#txtbox-invite-members").val()
                }
            })
                .done(function(data) {
                    var data_html = "";
                    $.each(data.members, function(index, value) {
                        if (index != (data.members.length - 1)) {
                            data_html = data_html +
                                "<div class=\"row\" style=\"border: 1px solid #000000;padding: 15px 0px;margin-bottom: 15px;\">";
                        } else {
                            data_html = data_html +
                                "<div class=\"row\" style=\"border: 1px solid #000000;padding: 15px 0px;\">";
                        }

                        data_html = data_html +
                                    "<div class=\"col-xs-4\">" +
                                        "<div class=\"thumbnail\" style=\"height: 90px;width: 90px;margin: 0px auto;\">" +
                                            "<img src=\"" + value.picture_file_name + "\" style=\"height: 80px;width: 80px;\">" +
                                        "</div>" +
                                    "</div>" +
                                    "<div class=\"col-xs-8\" style=\"position: relative;\">" +
                                        "<h3 style=\"margin-top: 15px;\">" + value.name + "</h3>" +
                                        "<p>" + value.steam32_id + "</p>" +
                                        "<div style=\"position: absolute;right: 10px;bottom: -15px;\">" +
                                            "<a role=\"button\" data-member-id=\"" + value.id + "\" class=\"btn-invite-members\">" +
                                                "<i class=\"fa fa-user-plus\" style=\"font-size: 24px;\"></i>" +
                                            "</a>" +
                                        "</div>" +
                                    "</div>" +
                                "</div>";
                    });

                    $("#invite-members-list-container").html(data_html);
                })
                .fail(function() {
                    $("#invite-members-list-container").empty();
                });
        } else {
            $("#invite-members-list-container").empty();
        }
    }

    $(document).on("click", ".btn-invite-members", function(e) {
        e.preventDefault();

        var that = this;
        var member_id = $(this).data("member-id");

        $.ajax({
            "type" : "PUT",
            "url" : api_url + "team/" + location.pathname.split("/")[2] + "/invite-member/" + member_id,
            "headers" : {
                "Accept" : "application/json",
                "Authorization" : "Bearer " + document.cookie.replace(/(?:(?:^|.*;\s*)participant_token\s*\=\s*([^;]*).*$)|^.*$/, "$1")
            },
            "beforeSend" : function() {
                $(that).parent().parent().parent().hide();
            }
        })
            .done(function(data) {
                if (data.code == 200) {
                    swal({
                        "title" : "Invite Members Success",
                        "text" : data.message[0],
                        "type" : "success",
                        "showConfirmButton" : false,
                        "timer" : 1000
                    });

                    $(that).parent().parent().parent().remove();
                } else {
                    var html_error_message = "";
                    $.each(data.message, function(index, value) {
                        if (html_error_message != "") {
                            html_error_message += "\n";
                        }

                        html_error_message += value;
                    });

                    swal({
                        "title" : "Invite Members Fail",
                        "text" : html_error_message,
                        "type" : "error"
                    });

                    if (data.code != 500) {
                        invite_members_done_typing();
                    } else {
                        $(that).parent().parent().parent().show();
                    }
                }
            })
            .fail(function() {
                swal({
                    "title" : "Invite Members Fail",
                    "text" : "Something went wrong. Please try again.",
                    "type" : "error"
                });

                $(that).parent().parent().parent().show();
            });
    });

    $(document).on("click", ".btn-kick-member", function(e) {
        e.preventDefault();

        var that = $(this);
        var member_id = $(this).data("member-id");
        var member_name = $(this).data("member-name");

        swal({
            "title" : "Kick \"" + member_name + "\"",
            "text" : "Do you really want to kick this member from team?",
            "type" : "warning",
            "showCancelButton" : true,
            "showConfirmButton" : true,
            "confirmButtonText" : "Yes, i want.",
            "cancelButtonText" : "No, i won't.",
            "closeOnConfirm" : false,
            "showLoaderOnConfirm" : true
        }, function() {
            $.ajax({
                "type" : "DELETE",
                "url" : api_url + "team/" + location.pathname.split("/")[2] + "/kick-member/" + member_id,
                "headers" : {
                    "Accept" : "application/json",
                    "Authorization" : "Bearer " + document.cookie.replace(/(?:(?:^|.*;\s*)participant_token\s*\=\s*([^;]*).*$)|^.*$/, "$1")
                }
            })
                .done(function(data) {
                    if (data.code == 200) {
                        that.parent().parent().parent().remove();

                        swal({
                            "title" : "Kick Member Success",
                            "text" : data.message[0],
                            "type" : "success",
                            "showConfirmButton" : false,
                            "timer" : 1000
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
                            "title" : "Kick Member Fail",
                            "text" : swal_text,
                            "type" : "error"
                        });
                    }
                })
                .fail(function() {
                    swal({
                        "title" : "Kick Member Fail",
                        "text" : "Something went wrong. Please try again.",
                        "type" : "error"
                    });
                });
        });
    });
});
