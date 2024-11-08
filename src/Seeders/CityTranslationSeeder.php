<?php

namespace InPunktoNET\Geonames\Seeders;

class CityTranslationSeeder extends TranslationSeeder
{
    /**
     * {@inheritdoc}
     */
    public static function translatableModel(): string
    {
        return CitySeeder::model();
    }
}
