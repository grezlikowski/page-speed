<?php

namespace Grezlikowski\PageSpeed\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User;

class PageSpeedTest extends Model
{
    protected $fillable = [
        'url',
        'strategy',
        'performance_score',
        'accessibility_score',
        'best_practices_score',
        'seo_score',
        'metrics',
        'raw_response',
        'user_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metrics' => 'array',
            'raw_response' => 'array',
            'performance_score' => 'integer',
            'accessibility_score' => 'integer',
            'best_practices_score' => 'integer',
            'seo_score' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model', 'App\\Models\\User'));
    }
}
