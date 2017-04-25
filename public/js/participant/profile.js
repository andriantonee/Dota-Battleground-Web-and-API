$(document).ready(function() {
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
                window.location.replace('/');
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
                var htmlErrorMessage = "";
                $.each(response.message, function(index, value) {
                    if (htmlErrorMessage != "") {
                        htmlErrorMessage += "\n";
                    }

                    htmlErrorMessage += value;
                });
                return htmlErrorMessage;
            }
        },
        "type" : "text",
        "url" : apiUrl + 'profile',
        "send" : "always",
        "title" : "Enter Name",
        "value" : function() {
            return $('#editable-name-value').text();
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
                window.location.replace('/');
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
                var htmlErrorMessage = "";
                $.each(response.message, function(index, value) {
                    if (htmlErrorMessage != "") {
                        htmlErrorMessage += "\n";
                    }

                    htmlErrorMessage += value;
                });
                return htmlErrorMessage;
            }
        },
        "type" : "text",
        "url" : apiUrl + 'profile',
        "send" : "always",
        "title" : "Enter E-mail",
        "value" : function() {
            return $('#editable-email-value').text();
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
                window.location.replace('/');
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
                if ($("#" + $(this).attr("aria-describedby")).find("input[type=\"text\"]").val().trim() == '') {
                    $("#editable-steam32_id-value").text('-');
                } else {
                    $("#editable-steam32_id-value").text($("#" + $(this).attr("aria-describedby")).find("input[type=\"text\"]").val());
                }

                return true;   
            } else {
                var htmlErrorMessage = "";
                $.each(response.message, function(index, value) {
                    if (htmlErrorMessage != "") {
                        htmlErrorMessage += "\n";
                    }

                    htmlErrorMessage += value;
                });
                return htmlErrorMessage;
            }
        },
        "type" : "text",
        "url" : apiUrl + 'profile',
        "send" : "always",
        "title" : "Enter Steam ID 32-bit",
        "value" : function() {
            if ($('#editable-steam32_id-value').text() != '-') {
                return $('#editable-steam32_id-value').text();
            } else {
                return '';
            }
        }
    });

    $("#profile-picture-action-delete").on("click", function(e) {
        e.preventDefault();

        swal({
            "title" : "Remove Profile Picture",
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
                "url" : apiUrl + "profile-picture",
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
                            "showConfirmButton" : false,
                            "timer" : 1000
                        });
                    } else {
                        swal({
                            "title" : "Remove Profile Picture Fail",
                            "text" : "Something went wrong. Please try again.",
                            "type" : "error"
                        });
                    }
                })
                .fail(function() {
                    swal({
                        "title" : "Remove Profile Picture Fail",
                        "text" : "Something went wrong. Please try again.",
                        "type" : "error"
                    });
                });
        });
    });

    $("#form-profile-picture").on("submit", function(e) {
        e.preventDefault();

        var formData = new FormData(this);
        var btn_save = Ladda.create(document.querySelector("#btn-save-form-profile-picture"));

        $.ajax({
            "type" : "POST",
            "url" : apiUrl + "profile-picture",
            "headers" : {
                "Accept" : "application/json",
                "Authorization" : "Bearer " + document.cookie.replace(/(?:(?:^|.*;\s*)participant_token\s*\=\s*([^;]*).*$)|^.*$/, "$1")
            },
            "data" : formData,
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
                    btn_login.stop();
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

        var formData = new FormData(this);
        var btn_save = Ladda.create(document.querySelector("#btn-save-form-settings"));

        $.ajax({
            "type" : "POST",
            "url" : apiUrl + "identification",
            "headers" : {
                "Accept" : "application/json",
                "Authorization" : "Bearer " + document.cookie.replace(/(?:(?:^|.*;\s*)participant_token\s*\=\s*([^;]*).*$)|^.*$/, "$1")
            },
            "data" : formData,
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
                    btn_login.stop();
                    $("#settings-alert-container").parent().removeClass("alert-success alert-danger").addClass("alert-danger");
                }
                $("#settings-alert-container").parent().show();
                $("#settings-alert-container").append(li_message);

                if (data.code == 200) {
                    $("#default-settings-picture-modal").attr("src", data.file_path);
                    $("#default-profile-picture-modal").attr("src", data.file_path);
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
});
