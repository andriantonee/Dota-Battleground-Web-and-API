<?php

namespace App\Events;

use App\Dota2LiveMatch;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class Dota2LiveMatchRadiantPlayersUpdated implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $match;
    // public $golds;
    // public $xps;
    public $abilities;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    // public function __construct(Dota2LiveMatch $match, array $golds, array $xps, array $abilities)
    public function __construct(Dota2LiveMatch $match, array $abilities)
    {
        $this->match = $match;
        // $this->golds = $golds;
        // $this->xps = $xps;
        $this->abilities = $abilities;
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
        return 'radiant_players_update';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        $radiant_players = $this->match->dota2_live_match_teams()
            ->select('id')
            ->where('side', 1)
            ->first()
            ->dota2_live_match_players()
            ->select('id', 'dota2_heroes_id', 'kills', 'death', 'assists', 'last_hits', 'denies', 'gold', 'level', 'gold_per_min', 'xp_per_min', 'respawn_timer', 'position_x', 'position_y', 'net_worth')
            ->with([
                'hero' => function($hero) {
                    $hero->select('id', 'name', 'picture_file_name');
                }
            ])
            ->get();

        // $golds = $this->golds;
        // $xps = $this->xps;
        $abilities = $this->abilities;

        return [
            'players' => $radiant_players,
            // 'golds' => $golds,
            // 'xps' => $xps,
            'abilities' => $abilities
        ];
    }
}
