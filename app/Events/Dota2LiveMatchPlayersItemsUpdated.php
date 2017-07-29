<?php

namespace App\Events;

use App\Dota2LiveMatch;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class Dota2LiveMatchPlayersItemsUpdated implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $match;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Dota2LiveMatch $match)
    {
        $this->match = $match;
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
        return 'players_items_update';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        $radiant_items = $this->match->dota2_live_match_teams()
            ->select('id')
            ->where('side', 1)
            ->first()
            ->dota2_live_match_players()
            ->select('id', 'name', 'members_id', 'dota2_heroes_id')
            ->with([
                'member' => function($member) {
                    $member->select('id', 'name');
                },
                'hero' => function($hero) {
                    $hero->select('id', 'name', 'picture_file_name');
                },
                'items' => function($items) {
                    $items->select('dota2_items.id AS id', 'dota2_items.name AS name', 'dota2_items.picture_file_name AS picture_file_name', 'dota2_live_matches_players_items.item_order AS item_order')
                        ->orderBy('dota2_live_matches_players_items.item_order', 'ASC');
                }
            ])
            ->get();

        $dire_items = $this->match->dota2_live_match_teams()
            ->select('id')
            ->where('side', 2)
            ->first()
            ->dota2_live_match_players()
            ->select('id', 'name', 'members_id', 'dota2_heroes_id')
            ->with([
                'member' => function($member) {
                    $member->select('id', 'name');
                },
                'hero' => function($hero) {
                    $hero->select('id', 'name', 'picture_file_name');
                },
                'items' => function($items) {
                    $items->select('dota2_items.id AS id', 'dota2_items.name AS name', 'dota2_items.picture_file_name AS picture_file_name', 'dota2_live_matches_players_items.item_order AS item_order')
                        ->orderBy('dota2_live_matches_players_items.item_order', 'ASC');
                }
            ])
            ->get();

        return [
            'radiant_items' => $radiant_items,
            'dire_items' => $dire_items
        ];
    }
}
