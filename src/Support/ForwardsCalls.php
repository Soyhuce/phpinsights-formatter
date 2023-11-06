<?php declare(strict_types=1);

namespace Soyhuce\PhpInsights\Support;

use BadMethodCallException;
use Error;

trait ForwardsCalls
{
    /**
     * Forward a method call to the given object.
     *
     * @param array<mixed> $parameters
     * @throws BadMethodCallException
     */
    protected function forwardCallTo(object $object, string $method, array $parameters): mixed
    {
        try {
            return $object->{$method}(...$parameters);
        } catch (Error|BadMethodCallException $e) {
            $pattern = '~^Call to undefined method (?P<class>[^:]+)::(?P<method>[^\(]+)\(\)$~';

            if (!preg_match($pattern, $e->getMessage(), $matches)) {
                throw $e;
            }

            if ($matches['class'] !== $object::class || $matches['method'] !== $method) {
                throw $e;
            }

            static::throwBadMethodCallException($method);
        }
    }

    /**
     * Throw a bad method call exception for the given method.
     *
     * @throws BadMethodCallException
     */
    protected static function throwBadMethodCallException(string $method): never
    {
        throw new BadMethodCallException(sprintf(
            'Call to undefined method %s::%s()',
            static::class,
            $method
        ));
    }
}
