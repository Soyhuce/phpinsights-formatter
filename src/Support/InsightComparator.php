<?php declare(strict_types=1);

namespace Soyhuce\Phpinsights\Support;

use NunoMaduro\PhpInsights\Domain\Contracts\Insight;

final class InsightComparator
{
    public function __invoke(Insight $first, Insight $second): int
    {
        $comparisons = [
            $this->titleComparison($first, $second),
            $this->insightsClassComparison($first, $second),
        ];

        foreach ($comparisons as $comparison) {
            if ($comparison !== 0) {
                return $comparison;
            }
        }

        return 0;
    }

    private function titleComparison(Insight $first, Insight $second): int
    {
        return $first->getTitle() <=> $second->getTitle();
    }

    private function insightsClassComparison(Insight $first, Insight $second): int
    {
        return $first->getInsightClass() <=> $second->getInsightClass();
    }
}
