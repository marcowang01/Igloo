<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Space;
use App\Models\Channel;
use App\Models\Block;
use Illuminate\Validation\Rule;

class BlockController extends Controller
{
    function create(Request $request, $channel_id)
    {
        $request->validate([
            'title' => 'required|string',
            'location' => 'required|string',
            'homepage_block' => ['required', Rule::in(['true','false'])],
            'message_block' => ['required', Rule::in(['true','false'])],
        ]);

        $homepage_block = ($request->homepage_block == 'true');
        $message_block = ($request->message_block == 'true');


        

        $block = new Block;
        $block->title = $request->title;
        $block->location = $request->location;
        $block->homepage_block = $homepage_block;
        $block->message_block = $message_block;
        $block->user_id = $request->user()->id;
        $channel = Channel::findOrFail($channel_id);
        $space = $channel->space;
        $block->space_id = $space->id;
        $block->channel_id = $channel_id;
        
        
        $block->space()->associate($space);
        $block->channel()->associate($channel);
        $block->save();

        return [
            "ret" => 0,
            "block" => $block
        ];
    }

    function block(Request $request, $block_id = null)
    {

        $block = Block::findOrFail($block_id);
        $block->makeVisible("messages");
        

        return [
            "ret" => 0,
            "block" => $block,
        ];
    }

    function messages(Request $request, $block_id = null)
    {

        $block = Block::findOrFail($block_id);

        return [
            "ret" => 0,
            "messages" => $block->messages,
        ];
    }

    function forward(Request $request, $channel_id, $block_id) {
        $old_block = Block::findOrFail($block_id);
        
        if ($old_block->channel->id == $channel_id) {
            return [
                "ret" => -1,
                "msg" => "Block cannot be forwarded to the same channel."
            ];
        }

        $block = new Block;
        $block->title = $old_block->title;
        $block->location = $old_block->location;
        $block->homepage_block = $old_block->homepage_block;
        $block->message_block = $old_block->message_block;
        $block->user_id = $old_block->user->id;
        
        $channel = Channel::findOrFail($channel_id);
        $space = $channel->space;
        $block->space_id = $space->id;
        $block->channel_id = $channel_id;        

        
        $block->space()->associate($space);
        $block->channel()->associate($channel);
        $block->save();

        return [
            "ret" => 0,
            "block" => $block
        ];
    }

    function delete(Request $request, $block_id)
    {

        $block = Block::findOrFail($block_id);
        if ($block->user_id != $request->user()->id) {
            abort(403);
        }

        foreach ($block->messages as $message) {
            if ($message->type == 'media') {
                foreach ($message->message_media as $medium) {
                    $medium->delete();
                }
            }
            $message->delete();
        }
        $block->delete();

        return [
            "ret" => 0,
        ];
    }
}
