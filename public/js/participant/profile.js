$(document).ready(function() {
    if (!has_identifications) {
        $("#settings-modal").modal("show");
    }

    $("#editable-name").editable({
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
                $("#editable-name-value").text($("#" + $(this).attr("aria-describedby")).find("input[type=\"text\"]").val());
                $("#navbar-login-name").text($("#" + $(this).attr("aria-describedby")).find("input[type=\"text\"]").val());

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
        "url" : api_url + "profile",
        "send" : "always",
        "title" : "Enter Name",
        "value" : function() {
            return $("#editable-name-value").text();
        }
    });

    $("#editable-email").editable({
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
                "email" : params.value
            };
            return new_params;
        },
        "placement" : "bottom",
        "success" : function(response, newValue) {
            if (response.code == 200) {
                $("#editable-email-value").text($("#" + $(this).attr("aria-describedby")).find("input[type=\"text\"]").val());

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
        "url" : api_url + "profile",
        "send" : "always",
        "title" : "Enter E-mail",
        "value" : function() {
            return $("#editable-email-value").text();
        }
    });

    $("#editable-steam32_id").editable({
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
                "steam32_id" : params.value
            };
            return new_params;
        },
        "placement" : "bottom",
        "success" : function(response, newValue) {
            if (response.code == 200) {
                if ($("#" + $(this).attr("aria-describedby")).find("input[type=\"text\"]").val().trim() == "") {
                    $("#editable-steam32_id-value").text("-");
                } else {
                    $("#editable-steam32_id-value").text($("#" + $(this).attr("aria-describedby")).find("input[type=\"text\"]").val());
                }

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
        "url" : api_url + "profile",
        "send" : "always",
        "title" : "Enter Steam ID 32-bit",
        "value" : function() {
            if ($("#editable-steam32_id-value").text() != "-") {
                return $("#editable-steam32_id-value").text();
            } else {
                return "";
            }
        }
    });

    $("#profile-picture-action-delete").on("click", function(e) {
        e.preventDefault();

        swal({
            "title" : "Remove Profile Picture",
            "text" : "Do you want to remove it?",
            "type" : "warning",
            "customClass" : "sweet-alert-custom",
            "showCancelButton" : true,
            "showConfirmButton" : true,
            "confirmButtonText" : "Yes, remove it.",
            "cancelButtonText" : "No, dont't remove it.",
            "closeOnConfirm" : false,
            "showLoaderOnConfirm" : true
        }, function() {
            $.ajax({
                "type" : "DELETE",
                "url" : api_url + "profile-picture",
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
                            "title" : "Remove Profile Picture Success",
                            "text" : data.message[0],
                            "type" : "success",
                            "customClass" : "sweet-alert-custom",
                            "showConfirmButton" : false,
                            "timer" : 1000
                        });
                    } else {
                        swal({
                            "title" : "Remove Profile Picture Fail",
                            "text" : "Something went wrong. Please try again.",
                            "type" : "error",
                            "customClass" : "sweet-alert-custom"
                        });
                    }
                })
                .fail(function() {
                    swal({
                        "title" : "Remove Profile Picture Fail",
                        "text" : "Something went wrong. Please try again.",
                        "type" : "error",
                        "customClass" : "sweet-alert-custom"
                    });
                });
        });
    });

    $("#form-profile-picture").on("submit", function(e) {
        e.preventDefault();

        var form_data = new FormData(this);
        var btn_save = Ladda.create(document.querySelector("#btn-save-form-profile-picture"));

        $.ajax({
            "type" : "POST",
            "url" : api_url + "profile-picture",
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

    $("#form-settings").on("submit", function(e) {
        e.preventDefault();

        var form_data = new FormData(this);
        var btn_save = Ladda.create(document.querySelector("#btn-save-form-settings"));

        $.ajax({
            "type" : "POST",
            "url" : api_url + "identification",
            "headers" : {
                "Accept" : "application/json",
                "Authorization" : "Bearer " + document.cookie.replace(/(?:(?:^|.*;\s*)participant_token\s*\=\s*([^;]*).*$)|^.*$/, "$1")
            },
            "data" : form_data,
            "contentType" : false,
            "processData" : false,
            "beforeSend" : function() {
                $("#settings-alert-container").parent().hide();
                $("#settings-alert-container").empty();
                btn_save.start();
            }
        })
            .done(function(data) {
                var li_message = "";
                $.each(data.message, function(index, value) {
                    li_message += "<li>" + value + "</li>";
                });
                if (data.code == 200) {
                    $("#settings-alert-container").parent().removeClass("alert-success alert-danger").addClass("alert-success");
                } else {
                    $("#settings-alert-container").parent().removeClass("alert-success alert-danger").addClass("alert-danger");
                }
                $("#settings-alert-container").parent().show();
                $("#settings-alert-container").append(li_message);

                if (data.code == 200) {
                    location.reload();
                    // $("#default-settings-picture-modal").attr("src", data.file_path);
                }
            })
            .fail(function() {
                $("#settings-alert-container").parent().removeClass("alert-success alert-danger").addClass("alert-danger");
                $("#settings-alert-container").parent().show();
                $("#settings-alert-container").append("<li>Something went wrong. Please try again.</li>");
            })
            .always(function() {
                btn_save.stop();
            });
    });

    $("#ckbox-join-password").on("change", function(e) {
        if ($(this).prop("checked")) {
            $("#txtbox-join-password").prop("disabled", false);
        } else {
            $("#txtbox-join-password").val("");
            $("#txtbox-join-password").prop("disabled", true);
        }
    });

    $("#form-create-team").on("submit", function(e) {
        e.preventDefault();

        var form_data = new FormData(this);
        var btn_create = Ladda.create(document.querySelector("#btn-create-form-create-team"));

        $.ajax({
            "type" : "POST",
            "url" : api_url + "team",
            "headers" : {
                "Accept" : "application/json",
                "Authorization" : "Bearer " + document.cookie.replace(/(?:(?:^|.*;\s*)participant_token\s*\=\s*([^;]*).*$)|^.*$/, "$1")
            },
            "data" : form_data,
            "contentType" : false,
            "processData" : false,
            "beforeSend" : function() {
                $("#create-team-alert-container").parent().hide();
                $("#create-team-alert-container").empty();
                btn_create.start();
            }
        })
            .done(function(data) {
                var li_message = "";
                $.each(data.message, function(index, value) {
                    li_message += "<li>" + value + "</li>";
                });
                if (data.code == 201) {
                    $("#create-team-alert-container").parent().removeClass("alert-success alert-danger").addClass("alert-success");
                } else {
                    $("#create-team-alert-container").parent().removeClass("alert-success alert-danger").addClass("alert-danger");
                }
                $("#create-team-alert-container").parent().show();
                $("#create-team-alert-container").append(li_message);

                if (data.code == 201) {
                    // var team_html = "" +
                    //     "<a class=\"team-list-content\" href=\"" + data.team.url + "\">" +
                    //         "<div class=\"row\" style=\"padding: 15px 5px;border: 1px solid #000000;margin: 0px;margin-bottom: 15px;\">" +
                    //             "<div class=\"col-xs-2\">" +
                    //                 "<div class=\"thumbnail\" style=\"margin: 0px auto;width: 75px;height: 75px;\">" +
                    //                     "<img src=\"" + data.team.picture_path + "\" style=\"width: 65px;height: 65px;\">" +
                    //                 "</div>" +
                    //             "</div>" +
                    //             "<div class=\"col-xs-10\">" +
                    //                 "<h3 style=\"margin-top: 12px;\">" + data.team.name + "</h3>" +
                    //                 "<h5>" + data.team.count + " Member</h5>" +
                    //             "</div>" +
                    //         "</div>" +
                    //     "</a>";
                    // $("#team-list-container").append(team_html);
                    location.reload();
                }
            })
            .fail(function() {
                $("#create-team-alert-container").parent().removeClass("alert-success alert-danger").addClass("alert-danger");
                $("#create-team-alert-container").parent().show();
                $("#create-team-alert-container").append("<li>Something went wrong. Please try again.</li>");
            })
            .always(function() {
                btn_create.stop();
            });
    });

    $("#show-barcode-modal").on("shown.bs.modal", function(e) {
        var btn_trigger = $(e.relatedTarget);

        $("#barcode-img").attr("src", btn_trigger.data("src"));
    });
});
