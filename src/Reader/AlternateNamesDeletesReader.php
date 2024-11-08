<?php

namespace InPunktoNET\Geonames\Reader;

class AlternateNamesDeletesReader implements Reader
{
    /**
     * The reader instance.
     *
     * @var Reader
     */
    protected $reader;

    /**
     * The record headers.
     *
     * @var array
     */
    protected $headers = [
        'alternateNameId',
        'geonameid',
        'alternate name',
        'comment',
    ];

    /**
     * Make a new reader instance.
     */
    public function __construct(Reader $reader)
    {
        $this->reader = new HeadersReader($reader, $this->headers);
    }

    /**
     * @inheritdoc
     */
    public function getRecords(string $path): iterable
    {
        return $this->reader->getRecords($path);
    }
}
