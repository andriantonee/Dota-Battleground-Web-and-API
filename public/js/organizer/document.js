$(document).ready(function(e) {
    $("#upload-business-document").on("submit", function(e) {
        e.preventDefault();

        var form_data = new FormData(this);
        var btn_upload = Ladda.create(document.querySelector("#btn-upload-business-document"));

        $.ajax({
            "type" : "POST",
            "url" : api_url + "document",
            "headers" : {
                "Accept" : "application/json",
                "Authorization" : "Bearer " + document.cookie.replace(/(?:(?:^|.*;\s*)organizer_token\s*\=\s*([^;]*).*$)|^.*$/, "$1")
            },
            "data" : form_data,
            "contentType" : false,
            "processData" : false,
            "beforeSend" : function() {
                $("#upload-business-document-alert-container").parent().hide();
                $("#upload-business-document-alert-container").empty();
                btn_upload.start();
            }
        })
            .done(function(data) {
                var li_message = "";
                $.each(data.message, function(index, value) {
                    li_message += "<li>" + value + "</li>";
                });
                if (data.code == 200) {
                    $("#upload-business-document-alert-container").parent().removeClass("alert-success alert-danger").addClass("alert-success");
                } else {
                    $("#upload-business-document-alert-container").parent().removeClass("alert-success alert-danger").addClass("alert-danger");
                }
                $("#upload-business-document-alert-container").parent().show();
                $("#upload-business-document-alert-container").append(li_message);

                if (data.code == 200) {
                    window.location.reload();
                }
            })
            .fail(function() {
                $("#upload-business-document-alert-container").parent().removeClass("alert-success alert-danger").addClass("alert-danger");
                $("#upload-business-document-alert-container").parent().show();
                $("#upload-business-document-alert-container").append("<li>Something went wrong. Please try again.</li>");
            })
            .always(function() {
                btn_upload.stop();
            });
    });
});
