<?php

namespace InPunktoNET\Geonames\Seeders;

class ContinentTranslationSeeder extends TranslationSeeder
{
    /**
     * {@inheritdoc}
     */
    public static function translatableModel(): string
    {
        return ContinentSeeder::model();
    }
}
