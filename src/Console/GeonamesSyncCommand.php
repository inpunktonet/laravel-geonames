<?php

namespace Nevadskiy\Geonames\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class GeonamesSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geonames:sync {--clean : Whether the directory with geonames downloads should be cleaned}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync the database according to the geonames dataset.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->sync($this->seeders());

        $this->clean();
    }

    /**
     * Sync database using given seeders.
     */
    protected function sync(array $seeders): void
    {
        foreach ($seeders as $seeder) {
            $seeder->sync();
        }
    }

    /**
     * Clean the geonames downloads directory.
     */
    private function clean(): void
    {
        if ($this->option('clean')) {
            (new Filesystem)->cleanDirectory(config('geonames.directory'));
        }
    }

    /**
     * Get the seeders list.
     * TODO: refactor using CompositeSeeder that resolves list automatically according to the config options.
     */
    protected function seeders(): array
    {
        return collect(config('geonames.seeders'))
            ->map(function ($seeder) {
                $seeder = resolve($seeder);

                // TODO: use LoggerAwareInterface
                if (method_exists($seeder, 'setLogger')) {
                    // TODO: add stack logger that uses file log (resolve from config)
                    $seeder->setLogger(new ConsoleLogger($this->getOutput(), [
                        LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
                        LogLevel::INFO => OutputInterface::VERBOSITY_NORMAL,
                    ]));
                }

                return $seeder;
            })
            ->all();
    }
}
