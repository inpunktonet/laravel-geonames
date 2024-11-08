<?php

namespace InPunktoNET\Geonames\Tests\Feature;

use InPunktoNET\Geonames\GeonamesDownloader;
use InPunktoNET\Geonames\Seeders\CitySeeder;
use InPunktoNET\Geonames\Seeders\CityTranslationSeeder;
use InPunktoNET\Geonames\Seeders\ContinentSeeder;
use InPunktoNET\Geonames\Seeders\ContinentTranslationSeeder;
use InPunktoNET\Geonames\Seeders\CountrySeeder;
use InPunktoNET\Geonames\Seeders\CountryTranslationSeeder;
use InPunktoNET\Geonames\Seeders\DivisionSeeder;
use InPunktoNET\Geonames\Seeders\DivisionTranslationSeeder;
use InPunktoNET\Geonames\Tests\Models\City;
use InPunktoNET\Geonames\Tests\Models\Continent;
use InPunktoNET\Geonames\Tests\Models\Country;
use InPunktoNET\Geonames\Tests\Models\Division;
use InPunktoNET\Geonames\Tests\TestCase;

class GeonamesSeedTest extends TestCase
{
    /** @test */
    public function it_seeds_geonames_dataset_into_database(): void
    {
        config(['geonames.seeders' => [
            ContinentSeeder::class,
            ContinentTranslationSeeder::class,
            CountrySeeder::class,
            CountryTranslationSeeder::class,
            DivisionSeeder::class,
            DivisionTranslationSeeder::class,
            CitySeeder::class,
            CityTranslationSeeder::class,
        ]]);

        ContinentSeeder::useModel(Continent::class);
        CountrySeeder::useModel(Country::class);
        DivisionSeeder::useModel(Division::class);
        CitySeeder::useModel(City::class);

        $service = $this->mock(GeonamesDownloader::class);

        $service->shouldReceive('downloadCountryInfo')
            ->andReturn($this->fixture('countryInfo.txt'));

        $service->shouldReceive('downloadAllCountries')
            ->andReturn($this->fixture('allCountries.txt'));

        $service->shouldReceive('downloadAlternateNamesV2')
            ->andReturn($this->fixture('alternateNamesV2.txt'));

        $this->artisan('geonames:seed');

        $this->assertDatabaseCount('continents', 1);
        $this->assertDatabaseCount('continent_translations', 3);
        $this->assertDatabaseCount('countries', 2);
        $this->assertDatabaseCount('country_translations', 6);
        $this->assertDatabaseCount('divisions', 8);
        $this->assertDatabaseCount('division_translations', 19);
        $this->assertDatabaseCount('cities', 9);
        $this->assertDatabaseCount('city_translations', 27);
    }
}
