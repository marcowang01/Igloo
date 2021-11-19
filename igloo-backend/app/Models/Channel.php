<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Channel extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'type',
        'visibility',
        'display_method',
        'can_send_message',
    ];


    protected $hidden = ['space'];

    protected $with = ['blocks'];

    public function space()
    {
        return $this->belongsTo(Space::class);
    }

    public function blocks()
    {
        return $this->hasMany(Block::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function message_extension_events()
    {
        return $this->hasMany(MessageExtensionEvent::class);
    }
}
