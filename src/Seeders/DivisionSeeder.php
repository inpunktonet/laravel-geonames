<?php

namespace InPunktoNET\Geonames\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use InPunktoNET\Geonames\Definitions\FeatureCode;
use function in_array;

class DivisionSeeder extends ModelSeeder
{
    /**
     * The seeder division model class.
     *
     * @var string
     */
    protected static $model = 'App\\Models\\Webservices\\Geo\\Division';

    /**
     * The allowed feature codes.
     *
     * @var array
     */
    protected $featureCodes = [
        FeatureCode::ADM1
    ];

    /**
     * The country list.
     *
     * @var array
     */
    protected $countries = [];

    /**
     * Use the given division model class.
     */
    public static function useModel(string $model): void
    {
        static::$model = $model;
    }

    /**
     * Get the division model class.
     */
    public static function model(): string
    {
        return static::$model;
    }

    /**
     * {@inheritdoc}
     */
    protected function loadResourcesBeforeMapping(): void
    {
        $this->countries = $this->newCountryModel()
            ->newQuery()
            ->pluck('id', 'code')
            ->all();
    }

    /**
     * Get a new country model instance.
     */
    protected function newCountryModel(): Model
    {
        return CountrySeeder::newModel();
    }

    /**
     * {@inheritdoc}
     */
    protected function unloadResourcesAfterMapping(): void
    {
        $this->countries = [];
    }

    /**
     * {@inheritdoc}
     */
    protected function filter(array $record): bool
    {
        return in_array($record['feature code'], $this->featureCodes, true);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapAttributes(array $record): array
    {
        return [
            'name' => $record['asciiname'] ?: $record['name'],
            'country_id' => $this->countries[$record['country code']],
            'latitude' => $record['latitude'],
            'longitude' => $record['longitude'],
            'timezone_id' => $record['timezone'],
            'population' => $record['population'],
            'elevation' => $record['elevation'],
            'dem' => $record['dem'],
            'code' => $record['admin1 code'],
            'feature_code' => $record['feature code'],
            'geoname_id' => $record['geonameid'],
            'created_at' => now(),
            'updated_at' => Carbon::createFromFormat('Y-m-d', $record['modification date'])->startOfDay(),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function updatable(): array
    {
        return [
            'name',
            'country_id',
            'latitude',
            'longitude',
            'timezone_id',
            'population',
            'elevation',
            'dem',
            'code',
            'feature_code',
            'updated_at',
        ];
    }
}
