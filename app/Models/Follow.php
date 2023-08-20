<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Follow extends Model
{
    use HasFactory;

    public function followed() {
        return $this->belongsTo(User::class, 'followedUser');
    }

    public function following() {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected $fillable = [
        'title',
        'body',
        'user_id'
    ];
}
