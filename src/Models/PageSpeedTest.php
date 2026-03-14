<?php

namespace Grezlikowski\PageSpeed\Models;

use Illuminate\Database\Eloquent\Model;

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

}
