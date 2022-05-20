<?php

namespace Soyhuce\Phpinsights\Support;

/**
 * @internal
 */
final class Arr
{
    /**
     * @param array<mixed> $array
     */
    public static function isAssoc(array $array): bool
    {
        $keys = array_keys($array);

        return array_keys($keys) !== $keys;
    }

    /**
     * @template TKey of array-key
     * @template TValue
     * @param array<TKey, TValue> $array
     * @param callable(TValue): bool $test
     * @return array{0: array<TKey, TValue>, 1: array<TKey, TValue>}
     */
    public static function partition(array $array, callable $test): array
    {
        $passed = [];
        $failed = [];

        foreach ($array as $key => $value) {
            if ($test($value)) {
                $passed[$key] = $value;
            } else {
                $failed[$key] = $value;
            }
        }

        if (!self::isAssoc($array)) {
            $passed = array_values($passed);
            $failed = array_values($failed);
        }

        return [$passed, $failed];
    }
}
