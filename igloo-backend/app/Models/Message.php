<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Message extends Model
{
    use HasFactory;
    protected $fillable = [
        'type',
        'message_extension_id',
        'message'
    ];

    protected $appends = ['message_media_links', 'message_extension_event', 'message_extension_forward'];

    protected $hidden = ['block', 'message_media', 'message_extension_id', 'is_channel'];

    protected $touches = ['block'];

    protected $with = ['user'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function block()
    {
        return $this->belongsTo(Block::class);
    }


    public function message_media()
    {
        return $this->hasMany(MessageMedia::class);
    }

    public function getMessageMediaLinksAttribute()
    {
        if ($this->message_media) {
            $urls = array();
            foreach ($this->message_media as $message_media) {
                $url = Storage::disk('s3_external')->temporaryUrl(
                    $message_media->media_file, now()->addMinutes(5)
                );
                $urls[] = $url;
            }

            return $urls;
        }
        return null;
    }


    public function getMessageExtensionEventAttribute()
    {
        if ($this->is_channel) {
            return Channel::findOrFail($this->message_extension_id);
        } else {
            return null;
        }
    }

    public function getMessageExtensionForwardAttribute()
    {
        if (!$this->is_channel && $this->message_extension_id) {
            return Block::findOrFail($this->message_extension_id);
        } else {
            return null;
        }
    }

    protected $casts = [
        'sent_at' => 'datatime',
    ];

}
