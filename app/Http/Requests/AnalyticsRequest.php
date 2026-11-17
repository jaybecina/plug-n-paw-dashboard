<?php

namespace App\Http\Requests;

use App\Enums\Period;
use App\Support\DateRange;
use BackedEnum;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Query-string input shared by every analytics page. Unknown ranges and
 * filter values fall back to defaults so a stale or hand-edited URL never
 * breaks the page. Access is checked by the `can:view-analytics` route
 * middleware.
 */
class AnalyticsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'clinic' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function dateRange(): DateRange
    {
        return DateRange::forPeriod($this->period());
    }

    public function period(): Period
    {
        return Period::tryFrom($this->string('range')->toString()) ?? Period::default();
    }

    /**
     * A filter value as an enum case, or null when absent or unknown.
     *
     * @template TEnum of BackedEnum
     *
     * @param  class-string<TEnum>  $enum
     * @return TEnum|null
     */
    public function enumFilter(string $key, string $enum): ?BackedEnum
    {
        return $enum::tryFrom($this->string($key)->toString());
    }
}
