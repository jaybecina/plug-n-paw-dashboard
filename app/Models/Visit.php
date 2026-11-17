<?php

namespace App\Models;

use App\Enums\Device;
use App\Enums\TrafficSource;
use Database\Factories\VisitFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * One browsing session on the public website.
 *
 * @property int $id
 * @property string $session_uuid
 * @property string $landing_page
 * @property TrafficSource $source
 * @property string|null $referrer_host
 * @property Device $device
 * @property string $country_code
 * @property int $page_views
 * @property int $duration_seconds
 * @property bool $bounced
 * @property Carbon $started_at
 */
class Visit extends Model
{
    /** @use HasFactory<VisitFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $guarded = ['id'];

    /**
     * @return HasMany<VetSearch, $this>
     */
    public function searches(): HasMany
    {
        return $this->hasMany(VetSearch::class);
    }

    protected function casts(): array
    {
        return [
            'source' => TrafficSource::class,
            'device' => Device::class,
            'page_views' => 'integer',
            'duration_seconds' => 'integer',
            'bounced' => 'boolean',
            'started_at' => 'datetime',
        ];
    }
}
