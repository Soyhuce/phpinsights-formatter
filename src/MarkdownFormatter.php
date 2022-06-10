<?php declare(strict_types=1);

namespace Soyhuce\PhpInsights;

use NunoMaduro\PhpInsights\Domain\Insights\InsightCollection;

class MarkdownFormatter extends FullMarkdownFormatter
{
    protected string $filename = 'insights.md';

    /**
     * @param array<string> $metrics
     */
    public function format(InsightCollection $insightCollection, array $metrics): void
    {
        $this->issues($insightCollection, $metrics);
        $this->write();
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     * @param array<\NunoMaduro\PhpInsights\Domain\Contracts\Insight> $insightsWithIssue
     */
    protected function issuesSubtitle(int $errors, array $insightsWithIssue): ?string
    {
        return null;
    }
}
