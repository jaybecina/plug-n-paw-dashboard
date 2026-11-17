<?php

namespace App\Models;

use Database\Factories\SupporterFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * A Ko-fi donation.
 *
 * @property int $id
 * @property string|null $name
 * @property int $amount_cents
 * @property string $currency
 * @property string|null $message
 * @property bool $is_monthly
 * @property Carbon $donated_at
 */
class Supporter extends Model
{
    /** @use HasFactory<SupporterFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'amount_cents' => 'integer',
            'is_monthly' => 'boolean',
            'donated_at' => 'datetime',
        ];
    }
}
