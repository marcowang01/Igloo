<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Space;
use App\Models\Channel;
use App\Models\Block;
use App\Models\Message;
use App\Models\MessageMedia;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Http;



class MessageController extends Controller
{
    function create(Request $request, $block_id)
    {
        $request->validate([
            'type' => ['required', Rule::in(['text', 'media', 'event', 'forward'])],
            'message' => 'string',
        ]);

        $message_extension_id = NULL;
        $is_channel = false;

        if (strcmp($request->type, 'event') == 0) {
            $request->validate(['target_channel_id' => 'required|integer']);
            if (!$request->event_time) {
                return [
                    "ret" => -1,
                    "msg" => "event time not found"
                ];
            } else {
                $current_date_time = Carbon::now()->toDateTimeString();
                $event_time = Carbon::parse($request->event_time);
                if($event_time->lt($current_date_time)){
                    return [
                        "ret" => -2,
                        "msg" => "cannot create an event in the past"
                    ];
                }
            }
            $message_extension_id = strval($request->target_channel_id);
            $is_channel = true;
        } else if (strcmp($request->type, 'forward') == 0) {
            $request->validate(['linked_block_id' => 'required|integer']);
            $message_extension_id = strval($request->linked_block_id);
        } else if (strcmp($request->type, 'media') == 0) {
            if (!$request->hasfile('media_file')) {
                return [
                    "ret" => -3,
                    "msg" => "media_file is invalid"
                ];
            }
        }

        $block = Block::findOrFail($block_id);

        $message = new Message;
        $message->type = $request->type;
        $message->message_extension_id = $message_extension_id;
        $message->message = $request->message;
        $message->user_id = $request->user()->id;
        $message->block_id = $block_id;

        $message->block()->associate($block);
        if ($is_channel) {
            $message->event_time = $event_time;
        }
        $message->save();

        $message->is_channel = $is_channel;

        if (strcmp($request->type, 'media') == 0) {
            foreach ($request->file('media_file') as $file) {
                $media = new MessageMedia;
                $path = $file->store('message-media');
                $media->media_file = $path;
                $media->message_id = $message->id;
                $media->message()->associate($message);
                $media->save();
            }
        }

        $space_id = $block->space->id;
        $space = Space::findOrFail($space_id);
        $unmuted_member_ids = [];
        foreach ($space->members as $member) {
            if ($member->id != $message->user_id && !$member->muted_spaces()->where("space_id", $space_id)->count()) {
                $unmuted_member_ids[] = $member->id;
            }
        }
        $sender_name = User::findOrFail($message->user_id)->name;
        send_notifications($unmuted_member_ids, "来自" . $sender_name . "的新消息", 
                            $message->message, ["navigateTo" => "block", "blockId" => $block_id]);

        return [
            "ret" => 0,
            "message" => $message,
        ];
    }

    function delete(Request $request, $message_id) {
        $message = Message::findOrFail($message_id);
        if ($message->user_id != $request->user()->id) {
            abort(403);
        }
        if ($message->type == 'media') {
            foreach ($message->message_media as $medium) {
                $medium->delete();
            }
        }
        $message->delete();
        return [
            "ret" => 0,
        ];
    }
}
