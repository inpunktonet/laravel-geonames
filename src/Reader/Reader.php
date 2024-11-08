<?php

namespace InPunktoNET\Geonames\Reader;

interface Reader
{
    /**
     * Get records by the given path.
     */
    public function getRecords(string $path): iterable;
}
