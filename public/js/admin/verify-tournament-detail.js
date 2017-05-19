$(document).ready(function (e) {
    $("#btn-approve-tournament").on("click", function(e) {
        e.preventDefault();

        var that = $(this);
        var tournament_name = $(this).data("tournament-name");

        swal({
            "title" : "Approve Tournament",
            "text" : "Do you really want to approve \"" + tournament_name + "\"",
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
                "url" : api_url + "tournament/" + location.pathname.split("/")[3] + "/approve",
                "headers" : {
                    "Accept" : "application/json",
                    "Authorization" : "Bearer " + document.cookie.replace(/(?:(?:^|.*;\s*)admin_token\s*\=\s*([^;]*).*$)|^.*$/, "$1")
                }
            })
                .done(function(data) {
                    if (data.code == 200) {
                        that.parent().parent().remove();

                        swal({
                            "title" : "Approve Tournament Success",
                            "text" : data.message[0],
                            "type" : "success",
                            "showConfirmButton" : false,
                            "timer" : 1000
                        });
                        window.location.replace(location.origin + "/admin");
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
                            "type" : "error"
                        });
                    }
                })
                .fail(function() {
                    swal({
                        "title" : "Approve Tournament Fail",
                        "text" : "Something went wrong. Please try again.",
                        "type" : "error"
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
                        that.parent().parent().remove();

                        swal({
                            "title" : "Decline Tournament Success",
                            "text" : data.message[0],
                            "type" : "success",
                            "showConfirmButton" : false,
                            "timer" : 1000
                        });
                        window.location.replace(location.origin + "/admin");
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
                            "type" : "error"
                        });
                    }
                })
                .fail(function() {
                    swal({
                        "title" : "Decline Tournament Fail",
                        "text" : "Something went wrong. Please try again.",
                        "type" : "error"
                    });
                });
        });
    });
});
