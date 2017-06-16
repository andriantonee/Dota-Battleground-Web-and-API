<?php

namespace App\Events;

use App\Dota2LiveMatch;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class Dota2LiveMatchDirePlayersUpdated implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $match;
    public $abilities;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Dota2LiveMatch $match, array $abilities)
    {
        $this->match = $match;
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
        return 'dire_players_update';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        $dire_players = $this->match->dota2_live_match_teams()
            ->select('id')
            ->where('side', 2)
            ->first()
            ->dota2_live_match_players()
            ->select('id', 'dota2_heroes_id', 'kills', 'death', 'assists', 'last_hits', 'denies', 'gold', 'level', 'gold_per_min', 'xp_per_min', 'respawn_timer', 'position_x', 'position_y', 'net_worth')
            ->with([
                'hero' => function($hero) {
                    $hero->select('id', 'name', 'picture_file_name');
                }
            ])
            ->get();

        $abilities = $this->abilities;

        return [
            'players' => $dire_players,
            'abilities' => $abilities
        ];
    }
}
