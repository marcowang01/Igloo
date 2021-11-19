<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Space;
use App\Models\Channel;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ChannelController extends Controller
{
    function create(Request $request, $space_id)
    {
        $space = Space::findOrFail($space_id);

        $request->validate([
            'name' => 'required|string',
            'type' => 'required|string',
            'visibility' => ['required', Rule::in(['secret', 'public'])],
            'can_send_message' => 'required', Rule::in(['true', 'false']),
            'display_method' => ['required', Rule::in(['feed', 'list', 'album', 'space_homepage',
                                                        'personal_homepage'])]
        ]);

        $can_send_message = ($request->can_send_message == 'true');

        $channel = new Channel;
        $channel->name = $request->name;
        $channel->type = $request->type;
        $channel->visibility = $request->visibility;
        $channel->can_send_message = $can_send_message;
        $channel->display_method = $request->display_method;
        $channel->space_id = $space_id;

        $channel->space()->associate($space);
        $channel->save();

        return [
            "ret" => 0,
            "channel" => $channel
        ];
    }

    function edit(Request $request, $channel_id)
    {
        $channel = Channel::findOrFail($channel_id);
        if ($channel->space->creator_id != $request->user()->id) {
            abort(403);
        }
        if ($request->has('name')) {
            $request->validate(['name' => 'string']);
            $channel->name = $request->name;
        }

        if ($request->has('type')) {
            $request->validate(['type' => 'string']);
            $channel->type = $request->type;
        }

        if ($request->has('visibility')) {
            $request->validate(['visibility' => ['required', Rule::in(['secret', 'public'])]]);
            $channel->visibility = $request->visibility;
        }

        if ($request->has('display_method')) {
            $request->validate(['display_method' => ['required', Rule::in(['feed','list','album'])]]);
            $channel->display_method = $request->display_method;
        }


        if ($request->has('can_send_message')) {
            $request->validate(['can_send_message' => ['required', Rule::in(['true','false'])]]);
            $can_send_message = ($request->can_send_message == 'true');
            $channel->can_send_message = $can_send_message;
        }


        $channel->save();

        return [
            "ret" => 0,
            "channel" => $channel
        ];
    }

    function channel(Request $request, $channel_id = null)
    {

        $channel = Channel::findOrFail($channel_id);
        $channel->makeVisible("space");
        $channel->space->makeHidden("channels");


        return [
            "ret" => 0,
            "channel" => $channel,
        ];
    }

    function delete(Request $request, $channel_id)
    {
        $channel = Channel::findOrFail($channel_id);
        if ($channel->space->creator_id != $request->user()->id) {
            abort(403);
        }

        foreach ($channel->blocks as $block) {
            foreach ($block->messages as $message) {
                if ($message->type == 'media') {
                    foreach ($message->message_media as $medium) {
                        $medium->delete();
                    }
                }
                $message->delete();
            }
            $block->delete();
        }

        $channel->delete();
        
        return [
            "ret" => 0,
        ];
    }
}
