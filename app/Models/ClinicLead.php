<?php

namespace App\Models;

use App\Enums\LeadAction;
use App\Enums\LeadStatus;
use Database\Factories\ClinicLeadFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A visitor clicked "Open in Maps" or "Call" on a clinic card.
 *
 * @property int $id
 * @property int $clinic_id
 * @property int|null $vet_search_id
 * @property LeadAction $action
 * @property LeadStatus $status
 * @property Carbon $occurred_at
 * @property Carbon|null $updated_at
 * @property-read Clinic $clinic
 */
class ClinicLead extends Model
{
    /** @use HasFactory<ClinicLeadFactory> */
    use HasFactory;

    public const CREATED_AT = null;

    protected $guarded = ['id'];

    /**
     * @return BelongsTo<Clinic, $this>
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * @return BelongsTo<VetSearch, $this>
     */
    public function search(): BelongsTo
    {
        return $this->belongsTo(VetSearch::class, 'vet_search_id');
    }

    protected function casts(): array
    {
        return [
            'action' => LeadAction::class,
            'status' => LeadStatus::class,
            'occurred_at' => 'datetime',
        ];
    }
}
