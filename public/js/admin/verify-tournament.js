$(document).ready(function (e) {
    $("#tournament-table").DataTable({
        "order" : [[6, "asc"]]
    });

    $("#tournament-table_paginate").find(".pagination").addClass("pagination-custom");

    $("#tournament-table tbody").on("click", "tr.tournament-table-row", function() {
        window.location.href = location.origin + "/admin/verify-tournament/" + $(this).data("id");
    });
});
