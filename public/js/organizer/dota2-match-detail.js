$(document).ready(function(e) {
    $(".scrollbar-macosx").scrollbar();

    var timestamp_label = [];
    var radiant_net_worth = [];
    var radiant_experience = [];
    var dire_net_worth = [];
    var dire_experience = [];
    for (var i = 0; i < duration_labels.length; i++) {
        var timestamp_min = Math.floor(duration_labels[i] / 60);
        var timestamp_sec = duration_labels[i] % 60;
        var timestamp = (timestamp_min < 10 ? "0" : "") + timestamp_min + ":" + (timestamp_sec < 10 ? "0" : "") + timestamp_sec;
        timestamp_label.push(timestamp);

        var net_worth = 0;
        var experience = 0;
        for (var j = 0; j < radiant_statistics.length; j++) {
            net_worth += radiant_statistics[j].golds[i].net_worth;
            experience += radiant_statistics[j].xps[i].xp;
        }
        radiant_net_worth.push(net_worth);
        radiant_experience.push(experience);

        var net_worth = 0;
        var experience = 0;
        for (var j = 0; j < dire_statistics.length; j++) {
            net_worth += dire_statistics[j].golds[i].net_worth;
            experience += dire_statistics[j].xps[i].xp;
        }
        dire_net_worth.push(net_worth);
        dire_experience.push(experience);
    }

    var net_worth_canvas = $("#net-worth-canvas");
    var net_worth_graph_config = {
        type : "line",
        data : {
            labels : timestamp_label,
            datasets : [{
                label : "Radiant",
                fill : false,
                backgroundColor : "#92A525",
                borderColor : "#92A525",
                data : radiant_net_worth,
                pointRadius: 0
            }, {
                label : "Dire",
                fill : false,
                backgroundColor : "#C23C2A",
                borderColor : "#C23C2A",
                data : dire_net_worth,
                pointRadius: 0
            }]
        },
        options : {
            tooltips : {
                mode : 'index',
                intersect : false,
            },
            hover : {
                mode : 'nearest',
                intersect : true
            },
            scales : {
                xAxes : [{
                    display : true,
                    scaleLabel : {
                        display : true,
                        labelString : 'Timestamp'
                    }
                }],
                yAxes : [{
                    display : true,
                    scaleLabel : {
                        display : true,
                        labelString : 'Net Worth'
                    }
                }]
            },
            responsive : true,
            maintainAspectRatio : false
        }
    };
    var net_worth_graph = new Chart(net_worth_canvas, net_worth_graph_config);

    var experience_canvas = $("#experience-canvas");
    var experience_graph_config = {
        type : "line",
        data : {
            labels : timestamp_label,
            datasets : [{
                label : "Radiant",
                fill : false,
                backgroundColor : "#92A525",
                borderColor : "#92A525",
                data : radiant_experience,
                pointRadius: 0
            }, {
                label : "Dire",
                fill : false,
                backgroundColor : "#C23C2A",
                borderColor : "#C23C2A",
                data : dire_experience,
                pointRadius: 0
            }]
        },
        options : {
            tooltips : {
                mode : 'index',
                intersect : false,
            },
            hover : {
                mode : 'nearest',
                intersect : true
            },
            scales : {
                xAxes : [{
                    display : true,
                    scaleLabel : {
                        display : true,
                        labelString : 'Timestamp'
                    }
                }],
                yAxes : [{
                    display : true,
                    scaleLabel : {
                        display : true,
                        labelString : 'Experience'
                    }
                }]
            },
            responsive : true,
            maintainAspectRatio : false
        }
    };
    var experience_graph = undefined;
    $("a[data-toggle=\"tab\"]").on("shown.bs.tab", function (e) {
        var target = $(e.target).attr("href");
        if (target == "#experience-tab") {
            if (typeof(experience_graph) == "undefined") {
                experience_graph = new Chart(experience_canvas, experience_graph_config);
            }
        }
    });

    // Enable pusher logging - don't include this in production
    Pusher.logToConsole = true;

    var pusher = new Pusher(PUSHER_APP_KEY, {
        "cluster" : "ap1",
        "encrypted" : true
    });

    var dota2_live_match_channel = pusher.subscribe("dota2-live-match" + location.pathname.split("/")[4]);

    dota2_live_match_channel.bind('update', function(data) {
        var net_worth_datasets = [];
        var experience_datasets = [];

        if (data.radiant.matches_result !== null && data.dire.matches_result !== null) {
            if (durationIntervalID !== false) {
                clearInterval(durationIntervalID);
                durationIntervalID = false;

                duration = data.match.duration;
                var timestamp_min = Math.floor(duration / 60);
                var timestamp_sec = duration % 60;
                var timestamp = (timestamp_min < 10 ? "0" : "") + timestamp_min + ":" + (timestamp_sec < 10 ? "0" : "") + timestamp_sec;
                duration_element.innerHTML = timestamp;
            }

            if (data.radiant.matches_result == 3) {
                $("#radiant-victory").show();
            }
            if (data.dire.matches_result == 3) {
                $("#dire-victory").show();
            }
        } else {
            if (durationIntervalID === false) {
                if (data.match.duration != 0) {
                    duration = data.match.duration;
                    durationIntervalID = setInterval(durationTick, 1000);
                }
            }
        }

        $("#spectators").html(data.match.spectators);
        $("#score").html(data.radiant.score + " - " + data.dire.score);

        for (var i = 0; i < data.radiant_picks.length; i++) {
            var pick_element = $("#radiant-pick-" + i);
            if (pick_element.length !== 0) {
                pick_element.attr("title", data.radiant_picks[i].name);
                if (data.radiant_picks[i].picture_file_name !== null) {
                    pick_element.attr("src", "/img/dota-2/heroes/" + data.radiant_picks[i].picture_file_name);
                }
                pick_element.removeAttr("id");
            }
        }
        for (var i = 0; i < data.radiant_bans.length; i++) {
            var ban_element = $("#radiant-ban-" + i);
            if (ban_element.length !== 0) {
                ban_element.attr("title", data.radiant_bans[i].name);
                if (data.radiant_bans[i].picture_file_name !== null) {
                    ban_element.attr("src", "/img/dota-2/heroes/" + data.radiant_bans[i].picture_file_name);
                }
                ban_element.removeAttr("id");
            }
        }
        var tower_status = ("00000000000").substr(0, 11 - parseInt(data.radiant.tower_state).toString(2).length) + parseInt(data.radiant.tower_state).toString(2);
        if (tower_status[0] == "1") {
            $(".radiant .tower.bottom-4").removeClass("destroy");
        } else {
            $(".radiant .tower.bottom-4").addClass("destroy");
        }
        if (tower_status[1] == "1") {
            $(".radiant .tower.top-4").removeClass("destroy");
        } else {
            $(".radiant .tower.top-4").addClass("destroy");
        }
        if (tower_status[2] == "1") {
            $(".radiant .tower.bottom-3").removeClass("destroy");
        } else {
            $(".radiant .tower.bottom-3").addClass("destroy");
        }
        if (tower_status[3] == "1") {
            $(".radiant .tower.bottom-2").removeClass("destroy");
        } else {
            $(".radiant .tower.bottom-2").addClass("destroy");
        }
        if (tower_status[4] == "1") {
            $(".radiant .tower.bottom-1").removeClass("destroy");
        } else {
            $(".radiant .tower.bottom-1").addClass("destroy");
        }
        if (tower_status[5] == "1") {
            $(".radiant .tower.middle-3").removeClass("destroy");
        } else {
            $(".radiant .tower.middle-3").addClass("destroy");
        }
        if (tower_status[6] == "1") {
            $(".radiant .tower.middle-2").removeClass("destroy");
        } else {
            $(".radiant .tower.middle-2").addClass("destroy");
        }
        if (tower_status[7] == "1") {
            $(".radiant .tower.middle-1").removeClass("destroy");
        } else {
            $(".radiant .tower.middle-1").addClass("destroy");
        }
        if (tower_status[8] == "1") {
            $(".radiant .tower.top-3").removeClass("destroy");
        } else {
            $(".radiant .tower.top-3").addClass("destroy");
        }
        if (tower_status[9] == "1") {
            $(".radiant .tower.top-2").removeClass("destroy");
        } else {
            $(".radiant .tower.top-2").addClass("destroy");
        }
        if (tower_status[10] == "1") {
            $(".radiant .tower.top-1").removeClass("destroy");
        } else {
            $(".radiant .tower.top-1").addClass("destroy");
        }
        var barrack_status = ("000000").substr(0, 6 - parseInt(data.radiant.barracks_state).toString(2).length) + parseInt(data.radiant.barracks_state).toString(2);
        if (barrack_status[0] == "1") {
            $(".radiant .barrack.bottom-ranged").removeClass("destroy");
        } else {
            $(".radiant .barrack.bottom-ranged").addClass("destroy");
        }
        if (barrack_status[1] == "1") {
            $(".radiant .barrack.bottom-melee").removeClass("destroy");
        } else {
            $(".radiant .barrack.bottom-melee").addClass("destroy");
        }
        if (barrack_status[2] == "1") {
            $(".radiant .barrack.middle-ranged").removeClass("destroy");
        } else {
            $(".radiant .barrack.middle-ranged").addClass("destroy");
        }
        if (barrack_status[3] == "1") {
            $(".radiant .barrack.middle-melee").removeClass("destroy");
        } else {
            $(".radiant .barrack.middle-melee").addClass("destroy");
        }
        if (barrack_status[4] == "1") {
            $(".radiant .barrack.top-ranged").removeClass("destroy");
        } else {
            $(".radiant .barrack.top-ranged").addClass("destroy");
        }
        if (barrack_status[5] == "1") {
            $(".radiant .barrack.top-melee").removeClass("destroy");
        } else {
            $(".radiant .barrack.top-melee").addClass("destroy");
        }
        net_worth_datasets[0] = 0;
        for (var i = 0; i < data.radiant_golds.length; i++) {
            net_worth_datasets[0] += parseInt(data.radiant_golds[i].net_worth);
        }
        experience_datasets[0] = 0;
        for (var i = 0; i < data.radiant_xps.length; i++) {
            experience_datasets[0] += parseInt(data.radiant_xps[i].xp);
        }

        for (var i = 0; i < data.dire_picks.length; i++) {
            var pick_element = $("#dire-pick-" + i);
            if (pick_element.length !== 0) {
                pick_element.attr("title", data.dire_picks[i].name);
                if (data.dire_picks[i].picture_file_name !== null) {
                    pick_element.attr("src", "/img/dota-2/heroes/" + data.dire_picks[i].picture_file_name);
                }
                pick_element.removeAttr("id");
            }
        }
        for (var i = 0; i < data.dire_bans.length; i++) {
            var ban_element = $("#dire-ban-" + i);
            if (ban_element.length !== 0) {
                ban_element.attr("title", data.dire_bans[i].name);
                if (data.dire_bans[i].picture_file_name !== null) {
                    ban_element.attr("src", "/img/dota-2/heroes/" + data.dire_bans[i].picture_file_name);
                }
                ban_element.removeAttr("id");
            }
        }
        var tower_status = ("00000000000").substr(0, 11 - parseInt(data.dire.tower_state).toString(2).length) + parseInt(data.dire.tower_state).toString(2);
        if (tower_status[0] == "1") {
            $(".dire .tower.bottom-4").removeClass("destroy");
        } else {
            $(".dire .tower.bottom-4").addClass("destroy");
        }
        if (tower_status[1] == "1") {
            $(".dire .tower.top-4").removeClass("destroy");
        } else {
            $(".dire .tower.top-4").addClass("destroy");
        }
        if (tower_status[2] == "1") {
            $(".dire .tower.bottom-3").removeClass("destroy");
        } else {
            $(".dire .tower.bottom-3").addClass("destroy");
        }
        if (tower_status[3] == "1") {
            $(".dire .tower.bottom-2").removeClass("destroy");
        } else {
            $(".dire .tower.bottom-2").addClass("destroy");
        }
        if (tower_status[4] == "1") {
            $(".dire .tower.bottom-1").removeClass("destroy");
        } else {
            $(".dire .tower.bottom-1").addClass("destroy");
        }
        if (tower_status[5] == "1") {
            $(".dire .tower.middle-3").removeClass("destroy");
        } else {
            $(".dire .tower.middle-3").addClass("destroy");
        }
        if (tower_status[6] == "1") {
            $(".dire .tower.middle-2").removeClass("destroy");
        } else {
            $(".dire .tower.middle-2").addClass("destroy");
        }
        if (tower_status[7] == "1") {
            $(".dire .tower.middle-1").removeClass("destroy");
        } else {
            $(".dire .tower.middle-1").addClass("destroy");
        }
        if (tower_status[8] == "1") {
            $(".dire .tower.top-3").removeClass("destroy");
        } else {
            $(".dire .tower.top-3").addClass("destroy");
        }
        if (tower_status[9] == "1") {
            $(".dire .tower.top-2").removeClass("destroy");
        } else {
            $(".dire .tower.top-2").addClass("destroy");
        }
        if (tower_status[10] == "1") {
            $(".dire .tower.top-1").removeClass("destroy");
        } else {
            $(".dire .tower.top-1").addClass("destroy");
        }
        var barrack_status = ("000000").substr(0, 6 - parseInt(data.dire.barracks_state).toString(2).length) + parseInt(data.dire.barracks_state).toString(2);
        if (barrack_status[0] == "1") {
            $(".dire .barrack.bottom-ranged").removeClass("destroy");
        } else {
            $(".dire .barrack.bottom-ranged").addClass("destroy");
        }
        if (barrack_status[1] == "1") {
            $(".dire .barrack.bottom-melee").removeClass("destroy");
        } else {
            $(".dire .barrack.bottom-melee").addClass("destroy");
        }
        if (barrack_status[2] == "1") {
            $(".dire .barrack.middle-ranged").removeClass("destroy");
        } else {
            $(".dire .barrack.middle-ranged").addClass("destroy");
        }
        if (barrack_status[3] == "1") {
            $(".dire .barrack.middle-melee").removeClass("destroy");
        } else {
            $(".dire .barrack.middle-melee").addClass("destroy");
        }
        if (barrack_status[4] == "1") {
            $(".dire .barrack.top-ranged").removeClass("destroy");
        } else {
            $(".dire .barrack.top-ranged").addClass("destroy");
        }
        if (barrack_status[5] == "1") {
            $(".dire .barrack.top-melee").removeClass("destroy");
        } else {
            $(".dire .barrack.top-melee").addClass("destroy");
        }
        net_worth_datasets[1] = 0;
        for (var i = 0; i < data.dire_golds.length; i++) {
            net_worth_datasets[1] += parseInt(data.dire_golds[i].net_worth);
        }
        experience_datasets[1] = 0;
        for (var i = 0; i < data.dire_xps.length; i++) {
            experience_datasets[1] += parseInt(data.dire_xps[i].xp);
        }

        if (data.radiant_golds.length !== 0 && data.dire_golds.length !== 0 && data.radiant_xps.length !== 0 && data.dire_xps.length !== 0) {
            var timestamp_min = Math.floor(data.match.duration / 60);
            var timestamp_sec = data.match.duration % 60;
            var timestamp = (timestamp_min < 10 ? "0" : "") + timestamp_min + ":" + (timestamp_sec < 10 ? "0" : "") + timestamp_sec;
            timestamp_label.push(timestamp);
            radiant_net_worth.push(net_worth_datasets[0]);
            dire_net_worth.push(net_worth_datasets[1]);
            radiant_experience.push(experience_datasets[0]);
            dire_experience.push(experience_datasets[1]);

            if (typeof(net_worth_graph) !== "undefined") {
                net_worth_graph.update();
            }
            if (typeof(experience_graph) !== "undefined") {
                experience_graph.update();
            }
        }
    });

    dota2_live_match_channel.bind('players_items_update', function(data) {
        for (var i = 0; i < data.radiant_items.length; i++) {
            var player = data.radiant_items[i];
            var player_element = $("#player-" + player.id + "-stats");
            if (player_element.length !== 0) {
                var player_items = {
                    1 : true,
                    2 : true,
                    3 : true,
                    4 : true,
                    5 : true,
                    6 : true
                };
                for (var j = 0; j < player.items.length; j++) {
                    var item_element = player_element.find(".item-" + player.items[j].item_order);
                    if (item_element.length !== 0) {
                        player_items[player.items[j].item_order] = false;
                        var item_element_img = item_element.find("img");
                        if (item_element_img.length !== 0) {
                            item_element_img.attr("title", player.items[j].name);
                            if (player.items[j].picture_file_name !== null) {
                                item_element_img.attr("src", "/img/dota-2/items/" + player.items[j].picture_file_name);
                            } else {
                                item_element_img.attr("src", "/img/dota-2/heroes/default.png");
                            }
                        }
                    }
                }
                for (var j = 1; j < 7; j++) {
                    if (player_items[j] === true) {
                        var item_element = player_element.find(".item-" + j);
                        if (item_element.length !== 0) {
                            var item_element_img = item_element.find("img");
                            if (item_element_img.length !== 0) {
                                item_element_img.removeAttr("title");
                                item_element_img.attr("src", "/img/dota-2/heroes/default.png");
                            }
                        }
                    }
                }
            }
        }

        for (var i = 0; i < data.dire_items.length; i++) {
            var player = data.dire_items[i];
            var player_element = $("#player-" + player.id + "-stats");
            if (player_element.length !== 0) {
                var player_items = {
                    1 : true,
                    2 : true,
                    3 : true,
                    4 : true,
                    5 : true,
                    6 : true
                };
                for (var j = 0; j < player.items.length; j++) {
                    var item_element = player_element.find(".item-" + player.items[j].item_order);
                    if (item_element.length !== 0) {
                        player_items[player.items[j].item_order] = false;
                        var item_element_img = item_element.find("img");
                        if (item_element_img.length !== 0) {
                            item_element_img.attr("title", player.items[j].name);
                            if (player.items[j].picture_file_name !== null) {
                                item_element_img.attr("src", "/img/dota-2/items/" + player.items[j].picture_file_name);
                            } else {
                                item_element_img.attr("src", "/img/dota-2/heroes/default.png");
                            }
                        }
                    }
                }
                for (var j = 1; j < 7; j++) {
                    if (player_items[j] === true) {
                        var item_element = player_element.find(".item-" + j);
                        if (item_element.length !== 0) {
                            var item_element_img = item_element.find("img");
                            if (item_element_img.length !== 0) {
                                item_element_img.removeAttr("title");
                                item_element_img.attr("src", "/img/dota-2/heroes/default.png");
                            }
                        }
                    }
                }
            }
        }
    });

    dota2_live_match_channel.bind('radiant_players_update', function(data) {
        for (var i = 0; i < data.players.length; i++) {
            var player = data.players[i];
            var player_element = $("#player-" + player.id + "-stats");
            if (player_element.length !== 0) {
                var img_hero_element = player_element.find("img.hero");
                if (img_hero_element.length !== 0 && player.hero !== null) {
                    img_hero_element.attr("title", player.hero.name);
                    if (player.hero.picture_file_name !== null) {
                        img_hero_element.attr("src", "/img/dota-2/heroes/" + player.hero.picture_file_name);
                    }
                    img_hero_element.removeClass("hero");
                }
                var kills_element = player_element.find(".kills");
                if (kills_element.length !== 0) {
                    kills_element.html(player.kills);
                }
                var death_element = player_element.find(".death");
                if (death_element.length !== 0) {
                    death_element.html(player.death);
                }
                var assists_element = player_element.find(".assists");
                if (assists_element.length !== 0) {
                    assists_element.html(player.assists);
                }
                var last_hits_element =  player_element.find(".last_hits");
                if (last_hits_element.length !== 0) {
                    last_hits_element.html(player.last_hits);
                }
                var denies_element = player_element.find(".denies");
                if (denies_element.length !== 0) {
                    denies_element.html(player.denies);
                }
                var net_worth_element = player_element.find(".net_worth");
                if (net_worth_element.length !== 0) {
                    var net_worth = player.net_worth;
                    if (net_worth > 1000) {
                        net_worth = Math.round(net_worth * 10 / 1000) / 10 + "k";
                    }
                    net_worth_element.html(net_worth);
                }
                if (player.hero !== null) {
                    if (duration != 0) {
                        var max_position_y = 7456;
                        var min_position_y = 7072;
                        var position_from_bottom = min_position_y + player.position_y;
                        var bottom = (position_from_bottom / (max_position_y + min_position_y) * 248);

                        var max_position_x = 7540;
                        var min_position_x = 7392;
                        var position_from_left = min_position_x + player.position_x;
                        var left = (position_from_left / (max_position_x + min_position_x) * 256);

                        var minimap_hero_icon_element = $("#player-" + player.id + "-hero-icon");
                        if (minimap_hero_icon_element.length === 0) {
                            if (player.hero.picture_file_name !== null) {
                                $("div.minimap .radiant").append("<div title=\"" + player.hero.name + "\" id=\"player-" + player.id + "-hero-icon\" class=\"player\" style=\"background-image: url(/img/dota-2/heroes/mini/" + player.hero.picture_file_name + ");bottom: " + bottom + "px;left: " + left + "px;\"></div>");
                            } else {
                                $("div.minimap .radiant").append("<div title=\"" + player.hero.name + "\" id=\"player-" + player.id + "-hero-icon\" class=\"player\" style=\"background-image: url(/img/dota-2/heroes/default.png);bottom: " + bottom + "px;left: " + left + "px;\"></div>");
                            }
                        } else {
                            minimap_hero_icon_element.css("bottom", bottom + "px");
                            minimap_hero_icon_element.css("left", left + "px");
                        }
                    }
                }
            }

            var player_abilities_element = $("#player-" + player.id + "-abilities");
            if (player_abilities_element.length !== 0) {
                var img_hero_element = player_abilities_element.find("img.hero");
                if (img_hero_element.length !== 0 && player.hero !== null) {
                    img_hero_element.attr("title", player.hero.name);
                    if (player.hero.picture_file_name !== null) {
                        img_hero_element.attr("src", "/img/dota-2/heroes/" + player.hero.picture_file_name);
                    }
                    img_hero_element.removeClass("hero");

                    if (player.hero.id == 74) {
                        player_abilities_element.find("td:gt(16)").remove();
                        for (var i = 17; i <= 25; i++) {
                            player_abilities_element.append("<td class=\"ability-" + i + "\"></td>");
                        }
                    }
                }
            }
        }

        var players_element = {};
        for (var i = 0; i < data.abilities.length; i++) {
            var ability = data.abilities[i];
            if (!players_element.hasOwnProperty(ability.id)) {
                var player_element = $("#player-" + ability.id + "-abilities");
                if (player_element.length !== 0) {
                    players_element[ability.id] = player_element;
                }
            }

            if (players_element.hasOwnProperty(ability.id)) {
                var player_element = players_element[ability.id];
                var player_ability_element = player_element.find(".ability-" + (ability.ability_order));
                if (player_ability_element.length !== 0) {
                    if (ability.picture_file_name !== null) {
                        var img_element = "<img title=\"" + ability.name + "\" class=\"img-sm\" src=\"/img/dota-2/abilities/" + ability.picture_file_name + "\">";
                    } else {
                        var img_element = "<img title=\"" + ability.name + "\" class=\"img-sm\" src=\"/img/dota-2/heroes/default.png\">";
                    }
                    player_ability_element.append(img_element);
                    player_ability_element.removeAttr("class");
                }
            }
        }
    });

    dota2_live_match_channel.bind('dire_players_update', function(data) {
        for (var i = 0; i < data.players.length; i++) {
            var player = data.players[i];
            var player_element = $("#player-" + player.id + "-stats");
            if (player_element.length !== 0) {
                var img_hero_element = player_element.find("img.hero");
                if (img_hero_element.length !== 0 && player.hero !== null) {
                    img_hero_element.attr("title", player.hero.name);
                    if (player.hero.picture_file_name !== null) {
                        img_hero_element.attr("src", "/img/dota-2/heroes/" + player.hero.picture_file_name);
                    }
                    img_hero_element.removeClass("hero");
                }
                var kills_element = player_element.find(".kills");
                if (kills_element.length !== 0) {
                    kills_element.html(player.kills);
                }
                var death_element = player_element.find(".death");
                if (death_element.length !== 0) {
                    death_element.html(player.death);
                }
                var assists_element = player_element.find(".assists");
                if (assists_element.length !== 0) {
                    assists_element.html(player.assists);
                }
                var last_hits_element =  player_element.find(".last_hits");
                if (last_hits_element.length !== 0) {
                    last_hits_element.html(player.last_hits);
                }
                var denies_element = player_element.find(".denies");
                if (denies_element.length !== 0) {
                    denies_element.html(player.denies);
                }
                var net_worth_element = player_element.find(".net_worth");
                if (net_worth_element.length !== 0) {
                    var net_worth = player.net_worth;
                    if (net_worth > 1000) {
                        net_worth = Math.round(net_worth * 10 / 1000) / 10 + "k";
                    }
                    net_worth_element.html(net_worth);
                }
                if (player.hero !== null) {
                    if (duration != 0) {
                        var max_position_y = 7456;
                        var min_position_y = 7072;
                        var position_from_bottom = min_position_y + player.position_y;
                        var bottom = (position_from_bottom / (max_position_y + min_position_y) * 248);

                        var max_position_x = 7540;
                        var min_position_x = 7392;
                        var position_from_left = min_position_x + player.position_x;
                        var left = (position_from_left / (max_position_x + min_position_x) * 256);

                        var minimap_hero_icon_element = $("#player-" + player.id + "-hero-icon");
                        if (minimap_hero_icon_element.length === 0) {
                            if (player.hero.picture_file_name !== null) {
                                $("div.minimap .dire").append("<div title=\"" + player.hero.name + "\" id=\"player-" + player.id + "-hero-icon\" class=\"player\" style=\"background-image: url(/img/dota-2/heroes/mini/" + player.hero.picture_file_name + ");bottom: " + bottom + "px;left: " + left + "px;\"></div>");
                            } else {
                                $("div.minimap .dire").append("<div title=\"" + player.hero.name + "\" id=\"player-" + player.id + "-hero-icon\" class=\"player\" style=\"background-image: url(/img/dota-2/heroes/default.png);bottom: " + bottom + "px;left: " + left + "px;\"></div>");
                            }
                        } else {
                            minimap_hero_icon_element.css("bottom", bottom + "px");
                            minimap_hero_icon_element.css("left", left + "px");
                        }
                    }
                }
            }

            var player_abilities_element = $("#player-" + player.id + "-abilities");
            if (player_abilities_element.length !== 0) {
                var img_hero_element = player_abilities_element.find("img.hero");
                if (img_hero_element.length !== 0 && player.hero !== null) {
                    img_hero_element.attr("title", player.hero.name);
                    if (player.hero.picture_file_name !== null) {
                        img_hero_element.attr("src", "/img/dota-2/heroes/" + player.hero.picture_file_name);
                    }
                    img_hero_element.removeClass("hero");

                    if (player.hero.id == 74) {
                        player_abilities_element.find("td:gt(16)").remove();
                        for (var i = 17; i <= 25; i++) {
                            player_abilities_element.append("<td class=\"ability-" + i + "\"></td>");
                        }
                    }
                }
            }
        }

        var players_element = {};
        for (var i = 0; i < data.abilities.length; i++) {
            var ability = data.abilities[i];
            if (!players_element.hasOwnProperty(ability.id)) {
                var player_element = $("#player-" + ability.id + "-abilities");
                if (player_element.length !== 0) {
                    players_element[ability.id] = player_element;
                }
            }

            if (players_element.hasOwnProperty(ability.id)) {
                var player_element = players_element[ability.id];
                var player_ability_element = player_element.find(".ability-" + (ability.ability_order));
                if (player_ability_element.length !== 0) {
                    if (ability.picture_file_name !== null) {
                        var img_element = "<img title=\"" + ability.name + "\" class=\"img-sm\" src=\"/img/dota-2/abilities/" + ability.picture_file_name + "\">";
                    } else {
                        var img_element = "<img title=\"" + ability.name + "\" class=\"img-sm\" src=\"/img/dota-2/heroes/default.png\">";
                    }
                    player_ability_element.append(img_element);
                    player_ability_element.removeAttr("class");
                }
            }
        }
    });

    $(".comment-open-action").on("click", function(e) {
        $("body").addClass("comment-open");
        $("body").append("<div class=\"comment-backdrop in\"></div>");
        $(".comment-container").css("right", "0");
        $(this).hide();
        $(".comment-close-action").show();
    });

    $(".comment-close-action").on("click", function(e) {
        $("body").removeClass("comment-open");
        $(".comment-backdrop.in").remove();
        $(".comment-container").css("right", "-425px");
        $(this).hide();
        $(".comment-open-action").show();
    });

    $("#post-comment").on("submit", function(e) {
        e.preventDefault();

        var data = $(this).serialize();
        var btn_post_comment = Ladda.create(document.querySelector("#btn-post-comment"));

        $.ajax({
            "type" : "POST",
            "url" : api_url + "dota-2/match/" + location.pathname.split("/")[4] + "/comment",
            "headers" : {
                "Accept" : "application/json",
                "Authorization" : "Bearer " + document.cookie.replace(/(?:(?:^|.*;\s*)organizer_token\s*\=\s*([^;]*).*$)|^.*$/, "$1")
            },
            "data" : data,
            "beforeSend" : function() {
                btn_post_comment.start();
            }
        })
            .done(function(data) {
                if (data.code == 200) {
                    $("#comment").val("");
                }
            })
            .fail(function() {
                
            })
            .always(function() {
                btn_post_comment.stop();
            });
    });

    dota2_live_match_channel.bind('comment_update', function(data) {
        var comment_html = "" +
            "<div class=\"comment-content\">" +
                "<div class=\"comment-profile\">";
        if (data.comment.member.picture_file_name !== null) {
            comment_html = comment_html +
                    "<img class=\"comment-profile-img\" src=\"/storage/member/" + data.comment.member.picture_file_name + "\">";
        } else {
            comment_html = comment_html +
                    "<img class=\"comment-profile-img\" src=\"/img/default-profile.jpg\">";
        }
        comment_html = comment_html +
                "</div> " +
                "<div class=\"comment-detail\">" +
                    "<p>" + data.comment.member.name + "</p>" +
                    "<p>" + data.comment.detail + "</p>" +
                "</div>" +
            "</div>";

        $("#comment-main").prepend(comment_html);
    });
});
