<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Seeders
    |--------------------------------------------------------------------------
    |
    | List of seeders that will be used to populate and update the database.
    | They will run one after the other so the order is important.
    |
    */

    'seeders' => [

        InPunktoNET\Geonames\Seeders\ContinentSeeder::class,
        InPunktoNET\Geonames\Seeders\ContinentTranslationSeeder::class,
        InPunktoNET\Geonames\Seeders\CountrySeeder::class,
        InPunktoNET\Geonames\Seeders\CountryTranslationSeeder::class,
        InPunktoNET\Geonames\Seeders\DivisionSeeder::class,
        InPunktoNET\Geonames\Seeders\DivisionTranslationSeeder::class,
        InPunktoNET\Geonames\Seeders\CitySeeder::class,
        InPunktoNET\Geonames\Seeders\CityTranslationSeeder::class,

    ],

    /*
    |--------------------------------------------------------------------------
    | Translations
    |--------------------------------------------------------------------------
    |
    | Translations are powered by the "nevadskiy/laravel-translatable" package.
    |
    */

    'translations' => [

        /*
         * The list of locales for which translations should be seeded.
         */
        'locales' => ['en', 'es', 'fr', 'de', 'it', 'pt', 'pl', 'uk', 'ru', 'ja', 'zh', 'hi', 'ar'],

        /*
         * Indicates if translations with a nullable locale should be seeded.
         * These type of translations can be useful for searching but take up more space.
         */
        'nullable_locale' => true,

    ],

    /*
    |--------------------------------------------------------------------------
    | Geonames downloads directory
    |--------------------------------------------------------------------------
    |
    | A temporary directory for geonames meta files and downloads.
    | It can be added to the .gitignore file.
    |
    */

    'directory' => storage_path('tmp'),

];
