<?php

namespace App\Models;

use App\Enums\SearchStatus;
use App\Enums\SearchType;
use Database\Factories\VetSearchFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * One call to the website's vet search.
 *
 * @property int $id
 * @property int|null $visit_id
 * @property SearchType $search_type
 * @property string|null $query
 * @property string $country_code
 * @property int $results_count
 * @property int $latency_ms
 * @property SearchStatus $status
 * @property Carbon $searched_at
 */
class VetSearch extends Model
{
    /** @use HasFactory<VetSearchFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $guarded = ['id'];

    /**
     * @return BelongsTo<Visit, $this>
     */
    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    /**
     * @return HasMany<ClinicLead, $this>
     */
    public function leads(): HasMany
    {
        return $this->hasMany(ClinicLead::class);
    }

    protected function casts(): array
    {
        return [
            'search_type' => SearchType::class,
            'status' => SearchStatus::class,
            'results_count' => 'integer',
            'latency_ms' => 'integer',
            'searched_at' => 'datetime',
        ];
    }
}
