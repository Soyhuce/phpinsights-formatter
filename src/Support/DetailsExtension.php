<?php declare(strict_types=1);

namespace Soyhuce\Phpinsights\Support;

use NunoMaduro\PhpInsights\Domain\Contracts\Insight;
use NunoMaduro\PhpInsights\Domain\Details;

/**
 * @method int getLine()
 * @method bool hasLine()
 * @method bool hasMessage()
 * @method string getMessage()
 * @method self setMessage(string $message)
 *
 * @see Details
 */
class DetailsExtension
{
    use ForwardsCalls;

    private Details $details;

    private Insight $insight;

    private string $category;

    public function __construct(?Details $details = null)
    {
        $this->details = $details ?? Details::make();
    }

    public static function make(?Details $details = null): self
    {
        return new self($details);
    }

    public function getDetails(): Details
    {
        return $this->details;
    }

    public function setInsight(Insight $insight): self
    {
        $this->insight = $insight;

        return $this;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getInsight(): Insight
    {
        return $this->insight;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * @param array<mixed> $arguments
     */
    public function __call(string $name, array $arguments): mixed
    {
        $result = $this->forwardCallTo($this->details, $name, $arguments);

        if ($result === $this->details) {
            return $this;
        }

        return $result;
    }
}
