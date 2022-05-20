<?php declare(strict_types=1);

namespace Soyhuce\PhpInsights\Support;

final class DetailsExtensionComparator
{
    public function __invoke(DetailsExtension $first, DetailsExtension $second): int
    {
        $comparisons = [
            $this->lineComparison($first, $second),
            $this->messageComparison($first, $second),
            $this->insightsClassComparison($first, $second),
        ];

        foreach ($comparisons as $comparison) {
            if ($comparison !== 0) {
                return $comparison;
            }
        }

        return 0;
    }

    private function lineComparison(DetailsExtension $first, DetailsExtension $second): int
    {
        return ($first->hasLine() ? $first->getLine() : null)
            <=> ($second->hasLine() ? $second->getLine() : null);
    }

    private function messageComparison(DetailsExtension $first, DetailsExtension $second): int
    {
        return ($first->hasMessage() ? $first->getMessage() : null)
            <=> ($second->hasMessage() ? $second->getMessage() : null);
    }

    private function insightsClassComparison(DetailsExtension $first, DetailsExtension $second): int
    {
        return $first->getInsight()->getInsightClass()
            <=> $second->getInsight()->getInsightClass();
    }
}
