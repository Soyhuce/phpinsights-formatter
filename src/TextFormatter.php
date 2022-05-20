<?php

declare(strict_types=1);

namespace Soyhuce\PhpInsights;

use NunoMaduro\PhpInsights\Application\Console\Contracts\Formatter;
use NunoMaduro\PhpInsights\Domain\Contracts\HasDetails;
use NunoMaduro\PhpInsights\Domain\Details;
use NunoMaduro\PhpInsights\Domain\DetailsComparator;
use NunoMaduro\PhpInsights\Domain\Insights\ForbiddenSecurityIssues;
use NunoMaduro\PhpInsights\Domain\Insights\InsightCollection;
use NunoMaduro\PhpInsights\Domain\Metrics\Architecture\Classes as ArchitectureClasses;
use NunoMaduro\PhpInsights\Domain\Metrics\Architecture\Files;
use NunoMaduro\PhpInsights\Domain\Metrics\Architecture\Globally as ArchitectureGlobally;
use NunoMaduro\PhpInsights\Domain\Metrics\Architecture\Interfaces as ArchitectureInterfaces;
use NunoMaduro\PhpInsights\Domain\Metrics\Architecture\Traits as ArchitectureTraits;
use NunoMaduro\PhpInsights\Domain\Metrics\Code\Classes;
use NunoMaduro\PhpInsights\Domain\Metrics\Code\Code;
use NunoMaduro\PhpInsights\Domain\Metrics\Code\Comments;
use NunoMaduro\PhpInsights\Domain\Metrics\Code\Functions;
use NunoMaduro\PhpInsights\Domain\Metrics\Code\Globally;
use NunoMaduro\PhpInsights\Domain\Metrics\Complexity\Complexity;
use NunoMaduro\PhpInsights\Domain\Results;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function count;

class TextFormatter implements Formatter
{
    public function __construct(
        protected InputInterface $input,
        protected OutputInterface $output,
    ) {
    }

    /**
     * @param array<string> $metrics
     */
    public function format(InsightCollection $insightCollection, array $metrics): void
    {
        $results = $insightCollection->results();

        $this->summary($results)
            ->code($insightCollection, $results)
            ->complexity($insightCollection, $results)
            ->architecture($insightCollection, $results)
            ->miscellaneous($results)
            ->issues($insightCollection, $metrics);
    }

    protected function summary(Results $results): self
    {
        $scores = [
            'code' => $results->getCodeQuality(),
            'complexity' => $results->getComplexity(),
            'architecture' => $results->getStructure(),
            'style' => $results->getStyle(),
        ];

        $this->renderTitleBlock('summary');
        $this->renderPercentageLines($scores);

        return $this;
    }

    protected function code(InsightCollection $insightCollection, Results $results): self
    {
        $this->renderTitleBlock(
            'code',
            sprintf(
                '%s pts within %s lines',
                $results->getCodeQuality(),
                (new Code())->getValue($insightCollection->getCollector())
            )
        );

        $scores = array_reduce(
            [Comments::class, Classes::class, Functions::class, Globally::class],
            static function ($results, $metric) use ($insightCollection): array {
                $name = explode('\\', $metric);

                /** @var \NunoMaduro\PhpInsights\Domain\Contracts\HasPercentage $metricInstance */
                $metricInstance = new $metric();

                $results[end($name)] = $metricInstance->getPercentage($insightCollection->getCollector());

                return $results;
            },
            []
        );

        $this->renderPercentageLines($scores);

        return $this;
    }

    protected function complexity(InsightCollection $insightCollection, Results $results): self
    {
        $this->renderTitleBlock(
            'complexity',
            sprintf(
                '%s pts with average of %s cyclomatic complexity',
                $results->getComplexity(),
                (new Complexity())->getAvg($insightCollection->getCollector())
            )
        );

        return $this;
    }

    protected function architecture(InsightCollection $insightCollection, Results $results): self
    {
        $this->renderTitleBlock(
            'architecture',
            sprintf(
                '%s pts within %s files',
                $results->getStructure(),
                (new Files())->getValue($insightCollection->getCollector())
            )
        );

        $scores = array_reduce(
            [
                ArchitectureClasses::class,
                ArchitectureInterfaces::class,
                ArchitectureGlobally::class,
                ArchitectureTraits::class,
            ],
            static function ($results, $metric) use ($insightCollection): array {
                $name = explode('\\', $metric);

                /** @var \NunoMaduro\PhpInsights\Domain\Contracts\HasPercentage $metricInstance */
                $metricInstance = new $metric();

                $results[end($name)] = $metricInstance->getPercentage($insightCollection->getCollector());

                return $results;
            },
            []
        );

        $this->renderPercentageLines($scores);

        return $this;
    }

    protected function miscellaneous(Results $results): self
    {
        $details = sprintf('%s pts on coding style', $results->getStyle());

        if ($results->hasInsightInCategory(ForbiddenSecurityIssues::class, 'Security')) {
            $details .= sprintf(
                ' and %s security issues encountered',
                $results->getTotalSecurityIssues()
            );
        }

        $this->renderTitleBlock('misc', $details);

        return $this;
    }

    /**
     * @param array<string> $metrics
     */
    protected function issues(InsightCollection $insightCollection, array $metrics): self
    {
        $detailsComparator = new DetailsComparator();

        foreach ($metrics as $metricClass) {
            /** @var \NunoMaduro\PhpInsights\Domain\Contracts\Metric $instance */
            $instance = new $metricClass();
            foreach ($insightCollection->allFrom($instance) as $insight) {
                if (!$insight->hasIssue()) {
                    continue;
                }

                $category = explode('\\', $metricClass);
                $category = $category[count($category) - 2];

                $issue = sprintf(
                    '• [%s] %s: (%s)',
                    $category,
                    $insight->getTitle(),
                    $insight->getInsightClass()
                );

                if (!$insight instanceof HasDetails) {
                    $this->output->writeln($issue);
                    $this->output->write(PHP_EOL);

                    continue;
                }

                $details = $insight->getDetails();
                usort($details, $detailsComparator);

                /** @var \NunoMaduro\PhpInsights\Domain\Details $detail */
                foreach ($details as $detail) {
                    $detailString = $this->formatFileLine($detail);

                    if ($detail->hasFunction()) {
                        $detailString .= ($detailString !== '' ? ':' : '') . $detail->getFunction();
                    }

                    if ($detail->hasMessage()) {
                        $detailString .= ($detailString !== '' ? ': ' : '') . $detail->getMessage();
                    }

                    $issue .= "\n  {$detailString}";
                }

                $this->output->writeln($issue);
                $this->output->write(PHP_EOL);
            }
        }

        return $this;
    }

    protected function renderTitleBlock(string $title, ?string $details = null): void
    {
        $this->output->write(sprintf('[%s]', mb_strtoupper($title, 'UTF-8')));

        if ($details !== null) {
            $this->output->write(' ' . $details);
        }

        $this->output->write(PHP_EOL);
        $this->output->write(PHP_EOL);
    }

    /**
     * @param array<string, float> $lines
     */
    protected function renderPercentageLines(array $lines): void
    {
        foreach ($lines as $name => $percentage) {
            $percentage = number_format($percentage, 1);
            $takenSize = mb_strlen($name . $percentage) + 4;

            $this->output->writeln(sprintf(
                '%s %s %s %%',
                mb_convert_case($name, MB_CASE_TITLE, 'UTF-8'),
                str_repeat('.', 70 - $takenSize),
                $percentage
            ));
        }
        $this->output->write(PHP_EOL);
    }

    protected function formatFileLine(Details $detail): string
    {
        $detailString = '';
        $basePath = realpath(getcwd()) . DIRECTORY_SEPARATOR;

        if ($detail->hasFile()) {
            $detailString .= str_replace($basePath, '', $detail->getFile());
        }

        if ($detail->hasLine()) {
            $detailString .= ($detailString !== '' ? ':' : '') . $detail->getLine();
        }

        return $detailString;
    }
}
