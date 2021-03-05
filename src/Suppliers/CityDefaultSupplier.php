<?php

namespace Nevadskiy\Geonames\Suppliers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Nevadskiy\Geonames\Models\City;
use Nevadskiy\Geonames\Models\Country;
use Nevadskiy\Geonames\Models\Division;
use Nevadskiy\Geonames\Support\Batch\Batch;

class CityDefaultSupplier extends DefaultSupplier implements CitySupplier
{
    /**
     * The city feature class.
     */
    public const FEATURE_CLASS = 'P';

    /**
     * The city feature codes.
     */
    public const FEATURE_CODES = ['PPL', 'PPLC', 'PPLA', 'PPLA2', 'PPLA3', 'PPLX', 'PPLG'];

    /**
     * Indicates the minimal population for being seeded.
     *
     * @var int
     */
    private $minPopulation;

    /**
     * The countries collection.
     *
     * @var Collection
     */
    protected $countries;

    /**
     * The divisions collection.
     *
     * @var Collection
     */
    protected $divisions;

    /**
     * Insert cities batch to reduce queries amount to be performed.
     *
     * @var Batch
     */
    protected $insertBatch;

    /**
     * Make a new supplier instance.
     */
    public function __construct(int $batchSize = 1000, int $minPopulation = 0)
    {
        $this->minPopulation = $minPopulation;
        $this->countries = $this->getCountries();
        $this->divisions = $this->getDivisions();
        $this->insertBatch = $this->makeInsertBatch($batchSize);
    }

    /**
     * Determine if the given data should be supplied.
     */
    protected function shouldSupply(array $data): bool
    {
        return $data['feature class'] === self::FEATURE_CLASS
            && in_array($data['feature code'], self::FEATURE_CODES, true)
            && (int) $data['population'] > $this->minPopulation;
    }

    /**
     * @inheritDoc
     */
    protected function findModel(int $id): ?Model
    {
        return City::query()
            ->where('geoname_id', $id)
            ->first();
    }

    /**
     * Perform the inserting process.
     *
     * @param array $data
     * @param int $id
     * @return bool
     */
    protected function performInsert(array $data, int $id): bool
    {
        $this->insertBatch->push($this->mapInsetFields($data, $id));

        return true;
    }

    /**
     * @inheritDoc
     */
    protected function updateModel(Model $model, array $data): bool
    {
        return $model->update($this->mapUpdateFields($data));
    }

    /**
     * @inheritDoc
     */
    protected function deleteModel(Model $model): bool
    {
        return $model->delete();
    }

    /**
     * Map fields for the city model.
     *
     * @param array $city
     * @param int $id
     * @return array
     */
    protected function mapInsetFields(array $city, int $id): array
    {
        return array_merge($this->mapUpdateFields($city), [
            'id' => City::generateId(),
            'geoname_id' => $id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Map fields for the city model.
     *
     * @param array $city
     * @return array
     */
    protected function mapUpdateFields(array $city): array
    {
        return [
            'name' => $city['asciiname'] ?: $city['name'],
            'country_id' => $this->getCountryId($city),
            'division_id' => $this->getDivisionId($city),
            'latitude' => $city['latitude'],
            'longitude' => $city['longitude'],
            'timezone_id' => $city['timezone'],
            'population' => $city['population'],
            'elevation' => $city['elevation'],
            'dem' => $city['dem'],
            'feature_code' => $city['feature code'],
            'modified_at' => $city['modification date'],
        ];
    }

    /**
     * Make a batch instance for better inserting performance.
     *
     * @param int $batchSize
     * @return Batch
     */
    protected function makeInsertBatch(int $batchSize): Batch
    {
        return new Batch(static function (array $cities) {
            DB::table(City::TABLE)->insert($cities);
        }, $batchSize);
    }

    /**
     * Get countries collection grouped by code.
     */
    protected function getCountries(): Collection
    {
        return Country::all()->keyBy('code');
    }

    /**
     * Get divisions collection grouped by country and code.
     */
    protected function getDivisions(): Collection
    {
        return Division::all()->groupBy(['country_id', 'code']);
    }

    /**
     * Get a country ID by the given city data.
     *
     * @param array $city
     * @return string
     */
    protected function getCountryId(array $city): string
    {
        return $this->countries[$city['country code']]->id;
    }

    /**
     * Get a division ID by the given city data.
     *
     * @param array $city
     * @return string
     */
    protected function getDivisionId(array $city): ?string
    {
        return $this->divisions[$this->getCountryId($city)][$city['admin1 code']][0]->id ?? null;
    }
}
