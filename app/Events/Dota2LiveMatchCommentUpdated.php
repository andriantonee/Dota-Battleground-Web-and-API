<?php

namespace App\Events;

use App\Dota2LiveMatchComment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class Dota2LiveMatchCommentUpdated implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $comment;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Dota2LiveMatchComment $comment)
    {
        $this->comment = $comment;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('dota2-live-match'.$this->comment->dota2_live_matches_id);
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'comment_update';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        $comment = $this->comment;
        $comment->load([
            'member' => function($member) {
                $member->select('id', 'name', 'picture_file_name');
            }
        ]);
        $comment_mobile = $comment->toArray();
        $comment->detail = str_replace(PHP_EOL, '<br />', $comment->detail);

        return [
            'comment' => $comment,
            'comment_mobile' => $comment_mobile
        ];
    }
}
