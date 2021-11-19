<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Space extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'location',
        'space_type',
        'visibility',
        'invite_mechanism',
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

    protected $hidden =[
        'members',
        'members_count',
        'channels',
        'avatar'
    ];

    protected $appends = ['avatar_link', 'is_muted', 'is_member'];

    public function getChannelsAttribute()
    {
        $space_id = $this->id;
        $channel_ids = DB::table('channels')->where('space_id', $space_id)->get()->pluck('id');

        if (!is_null($channel_ids) && count($channel_ids) > 0) {
            return Channel::whereIn('id', $channel_ids)->get();
        } else {
            return collect([]);
        }
    }

    public function getMembersCountAttribute()
    {
        if (is_null($this->members)) {
            return 0;
        } else {
            return $this->members->count();
        }
    }

    public function getIsMutedAttribute()
    {
        $user_loggedin = Auth::user();
        if($user_loggedin) {
            if ($user_loggedin->muted_spaces()->where("space_id", $this->id)->first()){
                return true;
            }
        }
        return false;
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

    public function getIsMemberAttribute()
    {
        $user_loggedin = Auth::user();
        if($user_loggedin) {
            if ($this->members()->where("member_id", $user_loggedin->id)->first()){
                return true;
            }
        }
        return false;
    }

    public function pending_members()
    {
        return $this->belongsToMany(User::class, 'space_member', 'space_id', 'member_id')
            ->wherePivot('status', 'pending')
            ->withPivot('inviter_id')
            ->withTimeStamps();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'space_member', 'space_id', 'member_id')
            ->wherePivot('status', 'yes')
            ->withTimeStamps();
    }


    public function channels()
    {
        return $this->hasMany(Channel::class);
    }

    public function blocks()
    {
        return $this->hasMany(Block::class);
    }
}
