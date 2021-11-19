<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageMedia extends Model
{
    use HasFactory;
    protected $fillable = [
        'media_file'
    ];

    public function message()
    {
        return $this->belongsTo(Message::class);
    }


    protected $casts = [
        'sent_at' => 'datatime',
    ];
}
