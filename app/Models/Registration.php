<?php

namespace App\Models;

use App\Enums\RegistrationStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'workshop_id', 'status'])]
class Registration extends Model
{
    protected function casts(): array
    {
        return [
            'status' => RegistrationStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
    }

    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('status', RegistrationStatus::Confirmed->value);
    }

    public function scopeWaiting(Builder $query): Builder
    {
        return $query->where('status', RegistrationStatus::Waiting->value);
    }
}
