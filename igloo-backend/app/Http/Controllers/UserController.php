<?php

namespace App\Http\Controllers;

use Cache;
use App\Models\Space;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Channel;
use App\Models\PushNotificationToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    function show_private_user_info($user) {
        // This may include sensitive information. Display this only for the user logged in.
        $user->makeVisible('phone_number');
        $user->makeVisible('phone_number_verified_at');
    }

    function login(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|size:11',
            'password' => 'required',
            'device_name' => 'required',
        ]);

        $user = User::where('phone_number', $request->phone_number)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            abort(403);
        }

        return [
            "ret"=>0,
            "token"=>$user->createToken($request->device_name)->plainTextToken
        ];
    }

    function user(Request $request, $id = null)
    {
        $user_logged_in = $request->user();

        if ($id === null) {
            self::show_private_user_info($user_logged_in);
            return $user_logged_in;
        }

        if ($user_logged_in->id == $id) {
            self::show_private_user_info($user_logged_in);
            return $user_logged_in;
        }

        $user = User::findOrFail($id);
        return $user;
    }

    function push_notification_register(Request $request)
    {
        $request->validate([
            'token' => "required|min:32|max:256"
        ]);

        $user = $request->user();

        $push_notification_token = PushNotificationToken::where("token", $request->token)->first();

        if ($push_notification_token) {
            return [
                "ret" => -1,
                "msg" => "Token already exists",
                "push_notification_token" => $push_notification_token
            ];
        }

        $push_notification_token = PushNotificationToken::create([
            "user_id" => $user->id,
            "token" => $request->token
        ]);

        return [
            "ret" => 0,
            "push_notification_token" => $push_notification_token
        ];
    }

    function push_notification_unregister(Request $request)
    {
        $request->validate([
            'token' => "required|min:32|max:256"
        ]);

        $user = $request->user();

        $token = $request->token;

        $push_notification_token = PushNotificationToken::where("user_id", $user->id)->where("token", $token)->firstOrFail();

        $push_notification_token->delete();

        return [
            "ret" => 0,
            "deleted_push_notification_token" => $token
        ];
    }

    function follow(Request $request, $user_id, $other_user_id)
    {
        // $user_id follows $other_user_id
        $user_logged_in = $request->user();
        if ($user_id != $user_logged_in->id) {
            abort(403);
        }

        $queried_user = User::findOrFail($other_user_id);

        if ($user_logged_in->followings->contains($queried_user->id)) {
            return [
                "ret" => -1,
                "msg" => "Already followed"
            ];
        }

        $user_logged_in->followings()->attach($queried_user);
        send_notifications([$other_user_id], "新的关注", $user_logged_in->name . "关注了你", ["navigateTo" => "notification"]);

        return [
            "ret" => 0
        ];
    }

    function unfollow(Request $request, $user_id, $other_user_id)
    {
        // $user_id unfollows $other_user_id
        $user_logged_in = $request->user();
        if ($user_id != $user_logged_in->id) {
            abort(403);
        }

        $queried_user = User::findOrFail($other_user_id);

        if (!$user_logged_in->followings->contains($queried_user->id)) {
            return [
                "ret" => -1,
                "msg" => "The user does not follow the other."
            ];
        }

        $user_logged_in->followings()->detach($queried_user);

        return [
            "ret" => 0
        ];
    }

    function search(Request $request)
    {
        $request->validate([
            'name' => "required|max:32|min:1"
        ]);

        $users = User::whereRaw( 'LOWER(`name`) LIKE ?', [
            strtolower(addcslashes($request->name, '%_')) . "%"
        ])->orWhereRaw('LOWER(`username`) = ?', [
            strtolower(addcslashes($request->name, '%_'))
        ])->get();

        return [
            "ret" => 0,
            "users" => $users
        ];
    }

    function friend_invite(Request $request, $user_id, $other_user_id)
    {
        // $user_id sends an invitation to $other_user_id
        $user_logged_in = $request->user();
        if ($user_id != $user_logged_in->id) {
            abort(403);
        }

        $queried_user = User::findOrFail($other_user_id);

        if ($user_logged_in->friends->contains($queried_user->id)) {
            return [
                "ret" => -1,
                "msg" => "Already friend"
            ];
        }

        DB::table('user_friend')->updateOrInsert([
            'user_id' => $user_id,
            'friend_id' => $queried_user->id,
        ],
        [
            'status' => "pending",
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        send_notifications([$other_user_id], "新的好友请求", $user_logged_in->name . "请求添加您为好友", ["navigateTo" => "notification"]);

        return [
            "ret" => 0
        ];
    }


    function avatar(Request $request) {
        $user = $request->user();

        $request->validate([
            'avatar' => 'required|max:10000|mimes:jpg,png,gif'
        ]);

        if (!$request->hasFile('avatar')) {
            return [
                "ret" => -1,
                "File is not found."
            ];
        }

        $avatar_path = $request->file('avatar')->store('avatars');

        $user->avatar = $avatar_path;
        $user->save();

        return [
            "ret" => 0,
            "user" => $user
        ];
    }

    function change_pwd(Request $request) {
        $user = $request->user();

        $request->validate([
            'new_password' => 'required|string|min:8',
            'current_password' => 'required|string'
        ]);

        if (!$user || !Hash::check($request->current_password, $user->password)) {
            abort(403);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        $user->tokens()->delete();

        return [
            "ret" => 0,
            "user" => $user
        ];
    }

    function update_friend_invititation(Request $request, $other_user_id = null) {
        $user = $request->user();

        $user_id = $user->id;

        $request->validate([
            'accept' => ['required', Rule::in(['true','false'])],
        ]);

        if (!DB::table('user_friend')->where([
            'user_id' => $other_user_id,
            'friend_id' => $user_id,
            'status' => "pending"
        ])->count()) {
            return [
                "ret" => -1,
                "msg" => "Request not found (already friend or already responded)."
            ];
        }

        if ($request->accept == "true") {
            $new_status = "yes";
        } else {
            $new_status = "no";
        }

        DB::table('user_friend')->where(['user_id' => $other_user_id,
            'friend_id' => $user_id,
            'status' => "pending"])->
        update([
            'status' => $new_status
        ]);
        
        if ($new_status) {
            send_notifications([$other_user_id], "好友请求更新", $user->name . "添加您为好友", ["navigateTo" => "notification"]);
        } else {
            send_notifications([$other_user_id], "好友请求更新", $user->name . "拒绝添加您为好友", ["navigateTo" => "notification"]);
        }
        

        return [
            "ret" => 0,
            "msg" => "New status is ".$new_status,
            "friends" => $user->friends,
            "user" => $user
        ];
    }

    function friends(Request $request, $id = null)
    {
        $user_logged_in = $request->user();
        if ($id != $user_logged_in->id) {
            abort(403);
        }
        return [
            "ret" => 0,
            "friends" => $user_logged_in->friends
        ];
    }

    function followers(Request $request, $id = null)
    {
        if ($id === null) {
            $user = $request->user();
        } else {
            $user = User::findOrFail($id);
        }
        return [
            "ret" => 0,
            "followers" => $user->followers
        ];
    }

    function followings(Request $request, $id = null)
    {
        if ($id === null) {
            $user = $request->user();
        } else {
            $user = User::findOrFail($id);
        }
        return [
            "ret" => 0,
            "followings" => $user->followings
        ];
    }

    function spaces(Request $request, $id = null)
    {
        if ($id === null) {
            $user = $request->user();
        } else {
            $user = User::findOrFail($id);
        }

        $spaces = $user->spaces;
        $filterd_spaces = [];
        foreach ($spaces as $space) {
            $visibility = $space->visibility;
            $authorized = false;

            if ($id == $request->user()->id){
                $authorized = true;
            } else if($visibility == "public"){
                $authorized = true;
            } else if ($visibility == "friends-only") {
                $authorized = $user->is_friend;
            } else if ($visibility == "admin-only") {
                $admin = User::findOrFail($space->creator_id);
                $authorized = $admin->is_friend;
            } else if ($visibility == "secret") {
                $authorized = $space->members()->where("member_id", $request->user()->id)->count();
            }

            if ($authorized){
                $filterd_spaces[] = $space;
            }
        }

        return [
            "ret" => 0,
            "spaces" => $filterd_spaces
        ];
    }

    function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return ["ret"=>0];
    }

    function register(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:8',
            'phone_number' => 'required|string|size:11',
            'name' => 'required',
            'username' => 'required|min:5'
        ]);

        if (User::where('phone_number', $request->phone_number)->first()) {
            return [
                "ret" => -1,
                "msg" => "User with same phone_number exists."
            ];
        }

        if (User::where('username', $request->username)->first()) {
            return [
                "ret" => -2,
                "msg" => "User with same username exists."
            ];
        }

        $user = User::create([
            "username" => $request->username,
            "name" => $request->name,
            "phone_number" => $request->phone_number,
            "password" => Hash::make($request->password)
        ]);
        self::show_private_user_info($user);

        $space = new Space();
        $space->name = $request->name." 的个人空间";
        $space->space_type = 'personal';
        $space->visibility = 'public';
        $space->invite_mechanism = 'none';
        $space->creator_id = $user->id;
        $space->save();

        $user->personal_space_id = $space->id;
        $user->save();

        $space->members()->attach($user->id, [
            'status' => 'yes', 'inviter_id' => $user->id
        ]);

        $space->channels()->createMany([
            [
                'name' => "homepage",
                'type' => 'list',
                'visibility' => 'public',
                'display_method' => 'personal_homepage',
                'can_send_message' => false
            ],
            [
                'name' => "circle",
                'type' => 'list',
                'visibility' => 'friends-only',
                'display_method' => 'list',
                'can_send_message' => false,
            ],
            [
                'name' => "favorites",
                'type' => 'list',
                'visibility' => 'secret',
                'display_method' => 'list',
                'can_send_message' => false,
            ]
        ]);

        return $user;
    }

    function notifications(Request $request){
        $user = $request->user();
        $time = $request->timeStamp;

        $space_invs = [];
        foreach($user->pending_spaces()->whereDate('space_member.updated_at', '>=', $time)->whereTime('space_member.updated_at', '>=', $time)->cursor() as $space){

            $space_invs[] = [
                "user" => User::find($space->pivot->inviter_id),
                "space" => Space::find($space->id),
                "timestamp" => $space->pivot->updated_at,
            ];
        }

        $follow_notification = [];
        foreach($user->followers()->whereDate('user_follower.updated_at', '>=', $time)->whereTime('user_follower.updated_at', '>=', $time)->cursor() as $follower){

            $follow_notification[] = [
                "user" => $follower,
                "timestamp" => $follower->pivot->updated_at,
            ];
        }

        $friend_requests = [];
        foreach($user->pending_requests()->whereDate('user_friend.updated_at', '>=', $time)->whereTime('user_friend.updated_at', '>=', $time)->cursor() as $friend){

            $friend_requests[] = [
                "user" => $friend,
                "timestamp" => $friend->pivot->updated_at,
            ];
        }

        $space_requests = [];
        foreach($user->created_spaces as $space){
            foreach($space->pending_members()->whereColumn('space_member.inviter_id','space_member.member_id')->whereDate('space_member.updated_at', '>=', $time)->whereTime('space_member.updated_at', '>=', $time)->cursor() as $member){
                $space_requests[] = [
                    "user" => $member,
                    "space" => $space,
                    "timestamp" => $member->pivot->updated_at,
                ];
            }
        }


        return[
            "invited_to_space" => $space_invs,
            "followed" => $follow_notification,
            "friend_request" => $friend_requests,
            "space_join_request" => $space_requests
        ];
    }

    function exit_space(Request $request, $space_id){

        $space = Space::findOrFail($space_id);

        $user = $request->user();

        if ($space->members()->where('member_id', $user->id)->first()){

            $space->members()->detach($user->id);

            $muted = $user->muted_spaces()->where('space_id',  $space_id)->first();
            if($muted){
                $user->muted_spaces()->detach($muted->id);
            }

            return [
                "ret" => 0,
                "message" => "successfully exited space"
            ];
        }

        return [
            "ret" => -1,
            "message" => "user is not a member of this space"
        ];
    }

    function edit(Request $request)
    {
        $request->validate([
            'phone_number' => 'nullable|string|size:11',
            'name' => 'nullable'
        ]);

        $user = $request->user();

        if ($request->has('phone_number')) {
            if (($user->phone_number != $request->phone_number) && User::where('phone_number', $request->phone_number)->count()) {
                return [
                    "ret" => -1,
                    "msg" => "Phone number exists."
                ];
            }
            $user->phone_number = intval($request->phone_number);
        }


        if ($request->has('name'))
            $user->name = $request->name;

        $user->save();

        return [
            "ret" => 0,
            "user" => $user
        ];
    }
}
