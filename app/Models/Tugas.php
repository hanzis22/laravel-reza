<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tugas extends Model
{
    protected $fillable = [
    'user_id',
    'judul',
    'file',
    'minggu',
    'status',
    'komentar',
    'ai_review',
];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
