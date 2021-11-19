<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Block extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'location',
        'homepage_block',
        'message_block',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];


    protected $touches = ['space'];

    protected $appends = ['first_message'];

    protected $hidden = ['space', 'channel', 'messages'];

    protected $with = ['user', 'messages'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function space()
    {
        return $this->belongsTo(Space::class);
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function first_message()
    {
        $message = $this->messages()->first();
        return $message;
    }

    public function message_extension_forwards()
    {
        return $this->hasMany(MessageExtensionForward::class);
    }

    public function getFirstMessageAttribute()
    {
        $message = $this->messages()->first();
        return $message;
    }

    public function getSpaceAttribute()
    {
        $space_id = $this->space_id;
        $space = Space::findOrFail($space_id);

        return $space;
    }
}
