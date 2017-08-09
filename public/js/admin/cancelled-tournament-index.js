$(document).ready(function (e) {
    $("#cancelled-tournament-table").DataTable({
        "order" : [[6, "asc"]],
        "drawCallback" : function(settings) {
            $("#cancelled-tournament-table_paginate").find(".pagination").addClass("pagination-custom");
        }
    });
});
