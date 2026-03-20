<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailDispatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipient_email',
        'mail_type',
        'unique_key',
        'subject',
        'context_hash',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];
}
