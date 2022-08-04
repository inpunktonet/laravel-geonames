<?php

namespace Nevadskiy\Geonames\Downloader;

use Nevadskiy\Downloader\Downloader;

class UnzipDownloader implements Downloader
{
    /**
     * The base downloader instance.
     *
     * @var Downloader
     */
    protected $downloader;

    /**
     * The unzipper instance.
     *
     * @var Unzipper
     */
    protected $unzipper;

    /**
     * Make a new downloader instance.
     */
    public function __construct(Downloader $downloader, Unzipper $unzipper)
    {
        $this->downloader = $downloader;
        $this->unzipper = $unzipper;
    }

    /**
     * @inheritdoc
     */
    public function download(string $url, string $destination = null): string
    {
        $destination = $this->downloader->download($url, $destination);

        if (! $this->unzipper->canBeUnzipped($destination)) {
            return $destination;
        }

        // TODO: add possibility to delete after unzipping...

        return $this->unzipper->unzip($destination);
    }
}
