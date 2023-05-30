<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\PushNotification
 *
 * @property int $id
 * @property string $type
 * @property string $title
 * @property string $body
 * @property array $data
 * @property int $user_id
 * @property User $user
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $read_at
 * @method static Builder|self newModelQuery()
 * @method static Builder|self newQuery()
 * @method static Builder|self query()
 * @method static Builder|self whereCreatedAt($value)
 * @method static Builder|self whereId($value)
 * @method static Builder|self whereUpdatedAt($value)
 * @mixin Eloquent
 */
class PushNotification extends Model
{
    protected $guarded = ['id'];
    protected $casts   = [
        'data' => 'array',
    ];

    const NEW_ORDER             = 'new_order';
    const NEW_USER_BY_REFERRAL  = 'new_user_by_referral';
    const STATUS_CHANGED        = 'status_changed';

    const TYPES = [
        self::NEW_ORDER             => self::NEW_ORDER,
        self::NEW_USER_BY_REFERRAL  => self::NEW_USER_BY_REFERRAL,
        self::STATUS_CHANGED        => self::STATUS_CHANGED,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}
