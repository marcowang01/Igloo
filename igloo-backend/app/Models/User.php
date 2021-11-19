<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'username',
        'phone_number',
        'password',
        'avatar'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'phone_number',
        'phone_number_verified_at',
        'pivot',

        // The following entries are hidden to prevent recursion in serialization.
        'friends',
        'followers',
        'followings',
        'spaces',
        // We display avatar link (the temporary link)
        'avatar'
    ];


    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * The attributes that should be added to serializations.
     *
     * @var array
     */
    protected $appends = [
        'friends_count',
        'followers_count',
        'followings_count',
        'spaces_count',
        'avatar_link',
        'is_following',
        'is_friend'
    ];


    public function personal_space()
    {
        return $this->hasOne(Space::class, 'personal_space_id');
    }

    public function getSpacesAttribute()
    {
        $user_id = $this->id;
        $space_ids = DB::table('space_member')->select('space_id')->where(function ($query) use ($user_id) {
            return $query->where('member_id', $user_id);
        })->get()->pluck('space_id');

        if (!is_null($space_ids) && count($space_ids) > 0) {
            $spaces = Space::whereIn('id', $space_ids)->get();
            return $spaces;
        } else {
            return collect([]);
        }
    }

    public function getFriendsAttribute()
    {
        $user_id = $this->id;
        $friend_ids = DB::table('user_friend')->select('friend_id')->where(function ($query) use ($user_id) {
            return $query->where('user_id', $user_id)->where('status', 'yes');
        })->orWhere(function ($query) use ($user_id) {
            return $query->where('friend_id', $user_id)->where('status', 'yes');
        })->get()->pluck('friend_id');

        if (!is_null($friend_ids) && count($friend_ids) > 0) {
            $friends = User::whereIn('id', $friend_ids)->get();
            return $friends;
        } else {
            return collect([]);
        }
    }


    public function getPersonalSpaceAttribute()
    {
        return Space::find($this->personal_space_id);
    }

    public function getIsFollowingAttribute()
    {
        $user_loggedin = Auth::user();
        if($user_loggedin) {
            return in_array($user_loggedin->id, $this->followers->pluck('id')->toArray());
        }
        return false;
    }

    public function getIsFriendAttribute()
    {
        $user_loggedin = Auth::user();
        if($user_loggedin) {
            $user_id = $this->id;
            return $this->friends()->where('id', $user_loggedin->id)->count() 
                || $user_loggedin->friends()->where("id", $user_id)->count();
        }
        return false;
    }

    public function pending_requests()
    {
        return $this->belongsToMany(User::class, 'user_friend', 'friend_id','user_id' )
            ->wherePivot('status', 'pending')
            ->withTimeStamps();
    }

    public function friends()
    {
        return $this->belongsToMany(User::class, 'user_friend', 'friend_id','user_id' )
            ->wherePivot('status', 'yes')
            ->withTimeStamps();
    }


    public function followings()
    {
        return $this->belongsToMany(User::class, 'user_follower', 'follower_id', 'user_id')
            ->withTimestamps();
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'user_follower', 'user_id', 'follower_id')
            ->withTimeStamps();
    }
    public function muted_friends()
    {
        return $this->belongsToMany(User::class, 'user_muted_friend', 'user_id', 'friend_id')
            ->withTimeStamps();
    }

    public function muted_spaces()
    {
        return $this->belongsToMany(Space::class, 'user_muted_space', 'user_id', 'space_id')
            ->withTimeStamps();
    }


    public function spaces()
    {
        return $this->belongsToMany(Space::class, 'space_member', 'member_id', 'space_id')
            ->withPivot('inviter_id')
            ->wherePivot('status', 'yes')
            ->withTimestamps();
    }


    public function pending_spaces()
    {
        return $this->belongsToMany(Space::class, 'space_member', 'member_id', 'space_id')
            ->withPivot('inviter_id')
            ->wherePivot('status', 'pending')
            ->withTimeStamps();
    }


    public function getFriendsCountAttribute()
    {
        if (is_null($this->friends)) {
            return 0;
        } else {
            return $this->friends->count();
        }
    }

    public function getFollowersCountAttribute()
    {
        if (is_null($this->followers)) {
            return 0;
        } else {
            return $this->followers->count();
        }
    }

    public function getFollowingsCountAttribute()
    {
        if (is_null($this->followings)) {
            return 0;
        } else {
            return $this->followings->count();
        }
    }

    public function getSpacesCountAttribute()
    {
        if (is_null($this->spaces()->get())) {
            return 0;
        } else {
            return $this->spaces()->get()->count();
        }
    }

    public function getAvatarLinkAttribute()
    {
        if ($this->avatar) {
            $url = Storage::disk('s3_external')->temporaryUrl(
                $this->avatar, now()->addMinutes(5)
            );
            return $url;
        }
        return null;
    }

    public function blocks()
    {
        return $this->hasMany(Block::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function created_spaces()
    {
        return $this->hasMany(Space::class, 'creator_id');
    }
}
