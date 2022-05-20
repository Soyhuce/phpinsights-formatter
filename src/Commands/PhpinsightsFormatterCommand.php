<?php declare(strict_types=1);

namespace Soyhuce\PhpinsightsFormatter\Commands;

use Illuminate\Console\Command;

class PhpinsightsFormatterCommand extends Command
{
    public $signature = 'phpinsights-formatter';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
