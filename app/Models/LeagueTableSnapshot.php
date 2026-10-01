<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeagueTableSnapshot extends Model
{
    protected $fillable = ['title', 'season', 'description', 'is_published', 'sort_order'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }

    public function rows(): HasMany
    {
        return $this->hasMany(LeagueTableRow::class)->orderBy('sort_order')->orderBy('position');
    }
}
