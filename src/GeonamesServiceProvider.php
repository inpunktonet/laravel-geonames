<?php

namespace InPunktoNET\Geonames;

use Illuminate\Console\Events\CommandStarting;
use Illuminate\Console\OutputStyle;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Nevadskiy\Downloader\CurlDownloader;
use Nevadskiy\Downloader\Downloader;
use InPunktoNET\Geonames\Downloader\ConsoleProgressDownloader;
use InPunktoNET\Geonames\Downloader\HistoryDownloader;
use InPunktoNET\Geonames\Reader\ConsoleProgressReader;
use InPunktoNET\Geonames\Reader\FileReader;
use InPunktoNET\Geonames\Reader\Reader;
use InPunktoNET\Geonames\Seeders\CompositeSeeder;
use InPunktoNET\Geonames\Support\CompositeLogger;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\SplFileInfo;

class GeonamesServiceProvider extends ServiceProvider
{
    /**
     * Register any package services.
     */
    public function register(): void
    {
        $this->registerConfig();
        $this->registerGeonamesDownloader();
        $this->registerFileDownloader();
        $this->registerFileReader();
        $this->registerCompositeSeeder();
        $this->registerGeonamesLogger();
    }

    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        $this->bootCommands();
        $this->swapConsoleOutput();
        $this->publishConfig();
        $this->publishMigrations();
        $this->publishModels();
        $this->publishSeeders();
    }

    /**
     * Register any package configurations.
     */
    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/geonames.php', 'geonames');
    }

    /**
     * Register the geonames downloader instance.
     */
    protected function registerGeonamesDownloader(): void
    {
        $this->app->when(GeonamesDownloader::class)
            ->needs('$directory')
            ->giveConfig('geonames.directory');
    }

    /**
     * Register the file downloader.
     */
    protected function registerFileDownloader(): void
    {
        $this->app->singleton(Downloader::class, function (Application $app) {
            $downloader = new CurlDownloader();
            $downloader->skipIfExists();
            $downloader->allowDirectoryCreation();

            if ($app->runningInConsole()) {
                $downloader->setLogger($app->make('geonames.logger'));
            }

            return $downloader;
        });

        if ($this->app->runningInConsole()) {
            $this->app->extend(Downloader::class, function (CurlDownloader $downloader, Application $app) {
                return new ConsoleProgressDownloader($downloader, $app->make(OutputStyle::class));
            });
        }

        $this->app->extend(Downloader::class, function (Downloader $downloader) {
            return new HistoryDownloader($downloader);
        });
    }

    /**
     * Register the file reader.
     */
    protected function registerFileReader(): void
    {
        $this->app->singleton(Reader::class, function () {
            return new FileReader();
        });

        if ($this->app->runningInConsole()) {
            $this->app->extend(Reader::class, function (Reader $reader, Application $app) {
                return new ConsoleProgressReader($reader, $app->make(OutputStyle::class));
            });
        }
    }

    /**
     * Register the composite seeder instance.
     */
    protected function registerCompositeSeeder(): void
    {
        $this->app->bind(CompositeSeeder::class, function (Application $app) {
            $seeders = collect(config('geonames.seeders'))
                ->map(function (string $seeder) use ($app) {
                    return $app->make($seeder);
                })
                ->all();

            $seeder = new CompositeSeeder(...$seeders);

            if ($app->runningInConsole()) {
                $seeder->setLogger($app->make('geonames.logger'));
            }

            return $seeder;
        });
    }

    /**
     * Register the geonames logger instance.
     */
    protected function registerGeonamesLogger(): void
    {
        $this->app->singletonIf('geonames.logger', function (Application $app) {
            return new CompositeLogger(
                new ConsoleLogger($app->make(OutputStyle::class), [
                    LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
                    LogLevel::INFO => OutputInterface::VERBOSITY_NORMAL,
                ]),
                $app->make('log')
            );
        });
    }

    /**
     * Boot any package commands.
     */
    protected function bootCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\GeonamesSeedCommand::class,
                Console\GeonamesSyncCommand::class,
                Console\GeonamesDailyUpdateCommand::class,
            ]);
        }
    }

    /**
     * Swap the console output instance.
     */
    protected function swapConsoleOutput(): void
    {
        if ($this->app->runningInConsole()) {
            $this->app[Dispatcher::class]->listen(CommandStarting::class, function (CommandStarting $command) {
                $this->app->instance(OutputStyle::class, new OutputStyle($command->input, $command->output));
            });
        }
    }

    /**
     * Publish any package configurations.
     */
    protected function publishConfig(): void
    {
        $this->publishes([
            __DIR__ . '/../config/geonames.php' => config_path('geonames.php'),
        ], 'geonames-config');
    }

    /**
     * Publish any package migrations.
     */
    protected function publishMigrations(): void
    {
        $this->publishes($this->stubPaths('database/migrations'), 'geonames-migrations');
    }

    /**
     * Publish any package models.
     */
    protected function publishModels(): void
    {
        $this->publishes($this->stubPaths('app/Models/Webservices/Geo'), 'geonames-models');
    }

    /**
     * Publish any package seeders.
     */
    protected function publishSeeders(): void
    {
        $this->publishes($this->stubPaths('database/seeders/Geo'), 'geonames-seeders');
    }

    /**
     * Get the stub paths for publishing by the given path.
     */
    protected function stubPaths(string $path): array
    {
        $path = trim($path, '/');

        return collect((new Filesystem())->allFiles(__DIR__ . '/../stubs/' . $path))
            ->mapWithKeys(function (SplFileInfo $file) use ($path) {
                return [$file->getPathname() => base_path($path . '/' . Str::replaceLast('.stub', '.php', $file->getFilename()))];
            })
            ->all();
    }
}
