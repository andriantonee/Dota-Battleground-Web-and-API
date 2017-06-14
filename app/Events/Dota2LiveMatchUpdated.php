<?php

namespace App\Events;

use App\Dota2LiveMatch;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class Dota2LiveMatchUpdated implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $match;
    public $radiant_golds;
    public $radiant_xps;
    public $dire_golds;
    public $dire_xps;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Dota2LiveMatch $match, array $radiant_golds, array $radiant_xps, array $dire_golds, array $dire_xps)
    {
        $this->match = $match;
        $this->radiant_golds = $radiant_golds;
        $this->radiant_xps = $radiant_xps;
        $this->dire_golds = $dire_golds;
        $this->dire_xps = $dire_xps;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('dota2-live-match'.$this->match->id);
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'update';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        $match = collect($this->match->toArray())->except(['matches_id', 'leagues_id', 'series_type', 'created_at', 'updated_at']);

        $radiant = $this->match->dota2_live_match_teams()
            ->select('id', 'score', 'tower_state', 'barracks_state', 'matches_result')
            ->where('side', 1)
            ->first();
        $radiant_picks = $radiant->heroes_pick()
            ->select('dota2_heroes.id AS id', 'dota2_heroes.name AS name', 'dota2_heroes.picture_file_name AS picture_file_name', 'dota2_live_matches_teams_picks.pick_order AS pick_order')
            ->orderBy('dota2_live_matches_teams_picks.pick_order', 'ASC')
            ->get();
        $radiant_bans = $radiant->heroes_ban()
            ->select('dota2_heroes.id AS id', 'dota2_heroes.name AS name', 'dota2_heroes.picture_file_name AS picture_file_name', 'dota2_live_matches_teams_bans.ban_order AS ban_order')
            ->orderBy('dota2_live_matches_teams_bans.ban_order', 'ASC')
            ->get();
        $radiant_golds = $this->radiant_golds;
        $radiant_xps = $this->radiant_xps;

        $dire = $this->match->dota2_live_match_teams()
            ->select('id', 'score', 'tower_state', 'barracks_state', 'matches_result')
            ->where('side', 2)
            ->first();
        $dire_picks = $dire->heroes_pick()
            ->select('dota2_heroes.id AS id', 'dota2_heroes.name AS name', 'dota2_heroes.picture_file_name AS picture_file_name', 'dota2_live_matches_teams_picks.pick_order AS pick_order')
            ->orderBy('dota2_live_matches_teams_picks.pick_order', 'ASC')
            ->get();
        $dire_bans = $dire->heroes_ban()
            ->select('dota2_heroes.id AS id', 'dota2_heroes.name AS name', 'dota2_heroes.picture_file_name AS picture_file_name', 'dota2_live_matches_teams_bans.ban_order AS ban_order')
            ->orderBy('dota2_live_matches_teams_bans.ban_order', 'ASC')
            ->get();
        $dire_golds = $this->dire_golds;
        $dire_xps = $this->dire_xps;

        return [
            'match' => $match,
            'radiant' => $radiant,
            'radiant_picks' => $radiant_picks,
            'radiant_bans' => $radiant_bans,
            'radiant_golds' => $radiant_golds,
            'radiant_xps' => $radiant_xps,
            'dire' => $dire,
            'dire_picks' => $dire_picks,
            'dire_bans' => $dire_bans,
            'dire_golds' => $dire_golds,
            'dire_xps' => $dire_xps
        ];
    }
}
