<?php declare(strict_types=1);

namespace Soyhuce\PhpinsightsFormatter;

use Soyhuce\PhpinsightsFormatter\Commands\PhpinsightsFormatterCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class PhpinsightsFormatterServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('phpinsights-formatter')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_phpinsights-formatter_table')
            ->hasCommand(PhpinsightsFormatterCommand::class);
    }
}
