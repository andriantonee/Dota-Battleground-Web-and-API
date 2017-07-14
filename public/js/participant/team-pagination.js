$(document).ready(function(e) {
    $("ul#team-pagination a").on("click", function(e) {
        e.preventDefault();

        if (!$(this).parent().hasClass("active")) {
            var action = $(this).attr("href");
            var all_team_pagination_a = $("ul#team-pagination a");
            var now_active = $("ul#team-pagination > li.active > a");
            var now_active_index = all_team_pagination_a.index(now_active);

            if (action == "#previous") {
                if (now_active_index != 1) {
                    all_team_pagination_a.eq(0).parent().removeClass("disabled");
                    all_team_pagination_a.eq(all_team_pagination_a.length - 1).parent().removeClass("disabled");

                    var previous_active_index = now_active_index - 1;
                    var previous_active = all_team_pagination_a.eq(previous_active_index);
                    var previous_active_target = previous_active.attr("href");

                    $(previous_active_target).show();
                    $(now_active.attr("href")).hide();
                    now_active.parent().removeClass("active");
                    $(previous_active).parent().addClass("active");

                    if (previous_active_index == 1) {
                        all_team_pagination_a.eq(0).parent().addClass("disabled")
                    } else if (previous_active_index == (all_team_pagination_a.length - 2)) {
                        all_team_pagination_a.eq(all_team_pagination_a.length - 1).parent().addClass("disabled");
                    }
                }
            } else if (action == "#next") {
                if (now_active_index != (all_team_pagination_a.length - 2)) {
                    all_team_pagination_a.eq(0).parent().removeClass("disabled");
                    all_team_pagination_a.eq(all_team_pagination_a.length - 1).parent().removeClass("disabled");

                    var next_active_index = now_active_index + 1;
                    var next_active = all_team_pagination_a.eq(next_active_index);
                    var next_active_target = next_active.attr("href");

                    $(next_active_target).show();
                    $(now_active.attr("href")).hide();
                    now_active.parent().removeClass("active");
                    $(next_active).parent().addClass("active");

                    if (next_active_index == 1) {
                        all_team_pagination_a.eq(0).parent().addClass("disabled")
                    } else if (next_active_index == (all_team_pagination_a.length - 2)) {
                        all_team_pagination_a.eq(all_team_pagination_a.length - 1).parent().addClass("disabled");
                    }
                }
            } else {
                all_team_pagination_a.eq(0).parent().removeClass("disabled");
                all_team_pagination_a.eq(all_team_pagination_a.length - 1).parent().removeClass("disabled");

                $(action).show();
                $(now_active.attr("href")).hide();
                now_active.parent().removeClass("active");
                $(this).parent().addClass("active");

                var this_index = all_team_pagination_a.index($(this));
                if (this_index == 1) {
                    all_team_pagination_a.eq(0).parent().addClass("disabled")
                } else if (this_index == (all_team_pagination_a.length - 2)) {
                    all_team_pagination_a.eq(all_team_pagination_a.length - 1).parent().addClass("disabled");
                }
            }
        }
    });
});
