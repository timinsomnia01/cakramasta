<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitorLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['user_id', 'ip_address', 'user_agent', 'path', 'visited_on'];

    protected function casts(): array
    {
        return ['visited_on' => 'date'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
