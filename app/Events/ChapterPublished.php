<?php

namespace App\Events;

use App\Models\Chapter;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * 章节发布事件
 */
class ChapterPublished
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Chapter $chapter)
    {
    }
}
