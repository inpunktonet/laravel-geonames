<?php

namespace InPunktoNET\Geonames\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use InPunktoNET\Geonames\Translations\HasTranslations;

/**
 * @property int id
 * @property string name
 * @property int country_id
 * @property int|null division_id
 * @property float latitude
 * @property float longitude
 * @property string|null timezone_id
 * @property int|null population
 * @property int|null elevation
 * @property int|null dem
 * @property string|null feature_code
 * @property int|null geoname_id
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 */
class City extends Model
{
    use HasTranslations;

    /**
     * Attributes that are translatable.
     *
     * @var array
     */
    protected $translatable = [
        'name',
    ];

    /**
     * Get a relationship with a country.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get a relationship with a division.
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }
}
