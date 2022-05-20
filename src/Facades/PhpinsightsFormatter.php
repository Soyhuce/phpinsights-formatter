<?php declare(strict_types=1);

namespace Soyhuce\PhpinsightsFormatter\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Soyhuce\PhpinsightsFormatter\PhpinsightsFormatter
 */
class PhpinsightsFormatter extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'phpinsights-formatter';
    }
}
