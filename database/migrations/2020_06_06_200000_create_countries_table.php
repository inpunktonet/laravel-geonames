<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Nevadskiy\Geonames\Geonames;
use Nevadskiy\Geonames\Models\Continent;
use Nevadskiy\Geonames\Models\Country;

class CreateCountriesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(Country::TABLE, static function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 2)->unique();
            $table->string('iso', 3)->unique();
            $table->string('iso_numeric', 3)->unique();
            $table->string('name');
            $table->string('name_official');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('timezone_id', 32)->nullable()->index();

            if (app(Geonames::class)->shouldSupplyContinents()) {
                $table->foreignUuid('continent_id')->index()->references('id')->on(Continent::TABLE)->cascadeOnDelete();
            }

            $table->string('capital')->nullable(); // Can be normalized using separate table.
            $table->string('currency_code', 3)->nullable(); // Can be normalized using separate table.
            $table->string('currency_name', 32)->nullable(); // Can be normalized using separate table.
            $table->string('tld', 3)->nullable();
            $table->string('phone_code', 24)->nullable();
            $table->string('postal_code_format', 100)->nullable();
            $table->string('postal_code_regex')->nullable();
            $table->string('languages')->nullable(); // Can be normalized using separate table.
            $table->string('neighbours')->nullable(); // Can be normalized using separate table.
            $table->float('area' 10, 2)->unsigned()->comment('In square kilometers.');
            $table->string('fips', 2)->nullable()->comment('Subject to change to iso code.');
            $table->bigInteger('population')->unsigned()->nullable();
            $table->smallInteger('dem')->nullable()->comment('Digital elevation model, srtm3 or gtopo30.');
            $table->string('feature_code', 10)->nullable()->comment('See: https://www.geonames.org/export/codes.html');
            $table->integer('geoname_id')->unsigned()->unique()->comment('Geonames database identifier.');
            $table->date('modified_at')->comment('Date of last modification in the geonames database.');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(Country::TABLE);
    }
}
