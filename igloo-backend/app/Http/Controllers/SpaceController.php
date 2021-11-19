<?php

namespace App\Http\Controllers;

use App\Models\Space;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class SpaceController extends Controller
{

    function create(Request $request)
    {
        $request->validate([
            'name' => 'nullable|string',
            'location' => 'nullable|string',
            'type' => ['required', Rule::in(['space', 'chat'])],
            'visibility' => ['required', Rule::in(['public', 'friends-only', 'admin-only', 'secret'])],
            'avatar' => 'nullable|string',
            'invite_mechanism' => ['required', Rule::in(['anyone_join', 'anyone_request', 'member_request', 'admin_request'])]
        ]);

        $space = new Space;
        $user = $request->user();
        if (!$request->name){
            if ($request->type == "chat"){
                $space->name = $user->name." 的群聊";
            } else {
                return [
                    "ret" => -1,
                    "msg" => "name field cannot be null"
                ];
            }
        } else{
            $space->name = $request->name;
        }

        $space->location = $request->location;
        if (!$request->location){
            $space->location = '';
        }

        $space->space_type = $request->type;
        $space->visibility = $request->visibility;
        $space->invite_mechanism = $request->invite_mechanism;
        $space->creator_id = $user->id;

        if ($space->space_type == "chat" && $space->visibility != "secret"){
            return [
                "ret" => -2,
                "msg" => "Space type and visibility incompatible."
            ];
        }

        $space->save();

        if ($request->type != "chat"){
            $space->channels()->create([
                'name' => "homepage",
                'type' => 'list',
                'visibility' => $request->visibility,
                'display_method' => 'space_homepage',
                'can_send_message' => false
            ]);
        }

        $space->channels()->createMany([
            [
                'name' => "feed",
                'type' => 'list',
                'visibility' => $request->visibility,
                'display_method' => 'feed',
                'can_send_message' => false,
            ],
            [
                'name' => "events",
                'type' => 'list',
                'visibility' => $request->visibility,
                'display_method' => 'list',
                'can_send_message' => false,
            ]
        ]);

        $member_ids = $request->invite_members;

        if ($request->invite_members){
            foreach($member_ids as $member_id){
                $space->members()->attach($member_id, [
                    'status'=>"pending",
                    'inviter_id'=>$user->id
                ]);
            }
        }

        $space->members()->attach($user->id, [
            'status'=>"yes",
            'inviter_id'=>$user->id
        ]);

        return $space;
    }

    function space(Request $request, $space_id = null )
    {

        $space = Space::findOrFail($space_id);
        $space->append(['channels'])->makeVisible(['channels']);

        return [
            "ret" => 0,
            "space" => $space,
        ];
    }

    function avatar(Request $request, $space_id) {
        $space = Space::findOrFail($space_id);

        if ($request->user()->id != $space->creator_id){
            abort(403);
        }

        $request->validate([
            'avatar' => 'required|max:10000|mimes:jpg,png,gif'
        ]);

        if (!$request->hasFile('avatar')) {
            return [
                "ret" => -1,
                "File is not found."
            ];
        }

        $avatar_path = $request->file('avatar')->store('space_avatars');

        $space->avatar = $avatar_path;
        $space->save();

        return [
            "ret" => 0,
            "space" => $space
        ];
    }

    function edit_title(Request $request, $space_id = null)
    {
        $space = Space::findOrFail($space_id);
        $user = $request->user();

        if ($space->creator_id == $user->id){
            $space->name = $request->title;
            $space->save();
            return[
                "ret" => 0,
                "msg" => "name change success"
            ];
        } else {
            abort(403);

        }
    }

    function edit_space(Request $request, $space_id)
    {
        $space = Space::findOrFail($space_id);
        $user = $request->user();

        if ($space->creator_id == $user->id){
            $space->name = $request->name;
            $space->location = $request->location;
            $space->invite_mechanism = $request->invite_mechanism;
            $space->save();

            return[
                "ret" => 0,
                "space" => $space
            ];
        } else {
            abort(403);
        }
    }

    function updates(Request $request)
    {
        $time = $request->last_update_timestamp;
        $user = $request->user();
        $filtered_blocks = [];

        if ($time){
            foreach ($user->spaces()->whereDate('spaces.updated_at', '>=', $time)->whereTime('spaces.updated_at', '>=', $time)->cursor()->where("is_muted", false) as $space){
                foreach ($space->blocks()->whereDate('blocks.updated_at', '>=', $time)->whereTime('blocks.updated_at', '>=', $time)->get() as $block){
                    $block->append(['space'])->makeVisible(['space']);
                    $filtered_blocks[] = $block;
                }
            }
        } else {
            foreach ($user->spaces()->cursor()->where('is_muted', false) as $space){
                foreach ($space->blocks()->get()->makeVisible(['space']) as $block){
                    $block->append(['space'])->makeVisible(['space']);
                    $filtered_blocks[] = $block;
                }
            }
        }

        return [
            "spaces" => $user->spaces()->get(),
            "msg_blocks" => $filtered_blocks
        ];
    }

    function members(Request $request, $space_id)
    {
        $space = Space::findOrFail($space_id);
        if ($space->members()->where("member_id", $request->user()->id)->count()){
            return $space->members()->get();
        }

        abort(403);
    }

    function accept_invitation(Request $request, $space_id, $member_id)
    {
        $space = Space::findOrFail($space_id);
        $user = $request->user();

        $member = $space->pending_members()->where('id', $member_id)->first();
        if (!$member){
            return [
                "ret" => -1,
                "msg" => "no such invite/request"
            ];
        }

        $is_request = $member->pivot->inviter_id == $member_id;

        if ($user->id == $member_id && $is_request){
            return [
                "ret" => -2,
                "msg" => "user cannot accept own request"
            ];
        }

        if ($user->id != $member_id && $is_request && $space->creator_id != $user->id){
            abort(403);
        }

        $member->pivot->status = "yes";
        $member->pivot->save();
        if ($is_request){
            $msg = "successfully accepted request";
        } else {
            $msg = "successfully accepted invite";
        }
        return [
            "ret" => 0,
            "msg" => $msg
        ];
    }

    function mute_space(Request $request, $space_id){
        $space = Space::findOrFail($space_id);

        $user = $request->user();

        if ($space->members()->where('member_id', $user->id)->count()){

            if(!$user->muted_spaces()->where('space_id', $space_id)->count()) {
                $user->muted_spaces()->attach($space->id);

                return [
                    "ret" => 0,
                    "msg" => "successfully muted space"
                ];
            }

            return [
                "ret" => -1,
                "msg" => "space is already muted"
            ];
        }

        return [
            "ret" => -2,
            "msg" => "user is not a member of this space"
        ];

    }

    function unmute_space(Request $request, $space_id){
        $space = Space::findOrFail($space_id);

        $user = $request->user();

        if ($space->members()->where('member_id', $user->id)->count()){

            if($user->muted_spaces()->where('space_id', $space_id)->count()){
                $user->muted_spaces()->detach($space_id);

                return [
                    "ret" => 0,
                    "msg" => "successfully unmuted space"
                ];
            }

            return [
                "ret" => -1,
                "msg" => "space is already unmuted"
            ];
        }

        return [
            "ret" => -2,
            "msg" => "user is not a member of this space"
        ];
    }

    function kick_member(Request $request, $space_id, $member_id)
    {

        $space = Space::findOrFail($space_id);
        $member = $space->members()->where("id", $member_id)->first();
        $user = $request->user();

        if ($space->creator_id = $user->id) {

            if ($member) {
                $space->members()->detach($member_id);

                return [
                    "ret" => 0,
                    "space" => $space ,
                    "msg" => "successfully kicked"
                ];
            }

            return [
                "ret" => -1,
                "space" => $space ,
                "msg" => "user not in space or already kicked"
            ];
        }

        abort(403);
    }

    function invite_member(Request $request, $space_id, $member_id)
    {
        $space = Space::findOrFail($space_id);
        User::findOrFail($member_id);

        $user = $request->user();
        $invite_mech = $space->invite_mechanism;
        $authorized = false;

        if (($invite_mech == "anyone_join" || $invite_mech == "anyone_request")
            && ($member_id == $user->id || $space->members()->where('member_id', $user->id)->count())){
                $authorized = true;
        } elseif ($invite_mech == "member_request"
            && $space->members()->where('member_id', $user->id)->count()){
            $authorized = true;
        } elseif ($space->creator_id  == $user->id){
            $authorized = true;
        }

        if ($authorized) {
            if ($space->members()->where('member_id', $member_id)->count()
                || $space->pending_members()->where('member_id', $member_id)->count()){
                return [
                    "ret" => -1,
                    "space" => $space ,
                    "msg" => "member already in space or already invited"
                ];
            }

            if ($invite_mech == "anyone_join" && $user->id == $member_id){
                $space->members()->attach($member_id, ['status' => "yes", 'inviter_id' => $user->id]);
                send_notifications([$member_id], "邀请加入空间", $user->name . "已将您加入:" . $space->name, ["navigateTo" => "notification"]);
            } else {
                $space->members()->attach($member_id, ['status' => "pending", 'inviter_id' => $user->id]);
                send_notifications([$member_id], "邀请加入空间", $user->name . "邀请您加入:" . $space->name, ["navigateTo" => "notification"]);
            }

            return [
                "ret" => 0,
                "space" => $space ,
                "msg" => "successfully invited member"
            ];
        }

        abort(403);
    }

    function invite_multiple_members(Request $request, $space_id)
    {
        $space = Space::findOrFail($space_id);
        $member_ids = $request->members_ids;

        $user = $request->user();
        $invite_mech = $space->invite_mechanism;
        $authorized = false;

        if (($invite_mech == "anyone_join" || $invite_mech == "anyone_request")
                && ((count($member_ids) == 1 && $member_ids[0] == $user->id)
                || $space->members()->where('member_id', $user->id)->count())){
            $authorized = true;
        } elseif ($invite_mech == "member_request"
            && $space->members()->where('member_id', $user->id)->count()){
            $authorized = true;
        } elseif ($space->creator_id  == $user->id){
            $authorized = true;
        }

        if ($authorized) {
            if (count($member_ids) == 1 && $invite_mech == "anyone_join" && $member_ids[0] == $user->id){
                $space->members()->attach($member_ids[0], ['status' => "yes", 'inviter_id' => $user->id]);
                send_notifications($member_ids, "邀请加入空间", $user->name . "已将您加入:" . $space->name, ["navigateTo" => "notification"]);
            } else {
                foreach ($member_ids as $member_id) {
                    if ($space->members()->where('member_id', $member_id)->count()
                        or $space->pending_members()->where('member_id', $member_id)->count()) {
                        return [
                            "ret" => -1,
                            "space" => $space,
                            "member_id" => $member_id,
                            "msg" => "this member is already in space or already invited"
                        ];
                    }

                    $space->members()->attach($member_id, ['status' => "pending", 'inviter_id' => $user->id]);
                }
                send_notifications($member_ids, "邀请加入空间", $user->name . "邀请您加入:" . $space->name, ["navigateTo" => "notification"]);
            }


            return [
                "ret" => 0,
                "space" => $space ,
                "msg" => "successfully invited members"
            ];
        }

        abort(403);
    }

    function search(Request $request) {
        $request->validate([
            'name' => "required|max:32|min:1"
        ]);

        $spaces = Space::whereRaw('LOWER(`name`) LIKE ?', [
            strtolower(addcslashes($request->name, '%_')) . "%"
        ])->get();

        return [
            "ret" => 0,
            "spaces" => $spaces
        ];
    }

}
