<?php

namespace App\Models;

use Database\Factories\ClinicFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A veterinary clinic that appeared in search results.
 *
 * @property int $id
 * @property string $name
 * @property string $city
 * @property string $country_code
 * @property float $lat
 * @property float $lon
 * @property string|null $phone
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Clinic extends Model
{
    /** @use HasFactory<ClinicFactory> */
    use HasFactory;

    protected $guarded = ['id'];

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
            'lat' => 'float',
            'lon' => 'float',
        ];
    }
}
