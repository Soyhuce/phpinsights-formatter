<?php declare(strict_types=1);

namespace Soyhuce\PhpInsights;

use NunoMaduro\PhpInsights\Domain\Contracts\HasDetails;
use NunoMaduro\PhpInsights\Domain\Details;
use NunoMaduro\PhpInsights\Domain\Insights\InsightCollection;
use Soyhuce\PhpInsights\Support\Arr;
use Soyhuce\PhpInsights\Support\DetailsExtension;
use Soyhuce\PhpInsights\Support\DetailsExtensionComparator;
use Soyhuce\PhpInsights\Support\InsightComparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use function assert;
use function count;

class FullMarkdownFormatter extends TextFormatter
{
    protected string $filename = 'insights-full.md';

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        parent::__construct(
            $input,
            new BufferedOutput($output->getVerbosity(), $output->isDecorated(), $output->getFormatter())
        );
    }

    /**
     * @param array<string> $metrics
     */
    public function format(InsightCollection $insightCollection, array $metrics): void
    {
        parent::format($insightCollection, $metrics);
        $this->write();
    }

    /**
     * @param array<string> $metrics
     */
    protected function issues(InsightCollection $insightCollection, array $metrics): self
    {
        $insightsWithIssue = [];
        $detailsWithoutFile = [];
        $detailsWithFile = [];
        $errors = 0;

        foreach ($metrics as $metricClass) {
            /** @var \NunoMaduro\PhpInsights\Domain\Contracts\Metric $instance */
            $instance = new $metricClass();
            foreach ($insightCollection->allFrom($instance) as $insight) {
                if (!$insight->hasIssue()) {
                    continue;
                }
                $insightsWithIssue[] = $insight;

                $category = explode('\\', $metricClass);
                $category = $category[count($category) - 2];

                if (!$insight instanceof HasDetails) {
                    $errors++;
                    $detailsWithoutFile[] = DetailsExtension::make()
                        ->setInsight($insight)
                        ->setCategory($category)
                        ->setMessage($insight->getTitle());

                    continue;
                }

                /** @var Details $detail */
                foreach ($insight->getDetails() as $detail) {
                    $errors++;

                    if (!$detail->hasFile()) {
                        $detailsWithoutFile[] = DetailsExtension::make($detail)
                            ->setInsight($insight)
                            ->setCategory($category);

                        continue;
                    }

                    $path = $this->normalizePath($detail);

                    $node = &$detailsWithFile;
                    foreach (explode(DIRECTORY_SEPARATOR, $path) as $part) {
                        $node[$part] ??= [];
                        $node = &$node[$part];
                    }
                    $node[] = DetailsExtension::make($detail)
                        ->setInsight($insight)
                        ->setCategory($category);
                }
            }
        }

        $this->renderTitleBlock(
            'issues',
            $this->issuesSubtitle($errors, $insightsWithIssue)
        );

        $this->renderWithFile($detailsWithFile);
        $this->renderWithoutFile($detailsWithoutFile);
        $this->renderInsightsLinks($insightsWithIssue);

        return $this;
    }

    protected function renderTitleBlock(string $title, ?string $details = null): void
    {
        $this->output->writeln(sprintf('# %s', mb_strtoupper($title, 'UTF-8')));

        if ($details !== null) {
            $this->output->write(PHP_EOL);
            $this->output->writeln($details . '  ');
        }

        $this->output->write(PHP_EOL);
    }

    /**
     * @param array<\NunoMaduro\PhpInsights\Domain\Contracts\Insight> $insightsWithIssue
     */
    protected function issuesSubtitle(int $errors, array $insightsWithIssue): ?string
    {
        return sprintf('%s issues on %s insights', $errors, count($insightsWithIssue));
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
                '%s %s %s %%  ',
                mb_convert_case($name, MB_CASE_TITLE, 'UTF-8'),
                str_repeat('.', 70 - $takenSize),
                $percentage
            ));
        }
        $this->output->write(PHP_EOL);
    }

    private function normalizePath(Details $detail): string
    {
        return str_replace(realpath(getcwd()) . DIRECTORY_SEPARATOR, '', $detail->getFile());
    }

    /**
     * @param array<string, mixed> $tree
     */
    protected function renderWithFile(array $tree, int $level = 0): void
    {
        if (Arr::isAssoc($tree)) {
            [$folders, $files] = Arr::partition($tree, static fn (array $subtree) => Arr::isAssoc($subtree));
            ksort($folders);
            ksort($files);

            foreach ($folders as $path => $subtree) {
                $this->output->writeln(sprintf(
                    '%s- %s',
                    str_repeat(' ', $level * 4),
                    $path
                ));
                $this->renderWithFile($subtree, $level + 1);
            }

            foreach ($files as $file => $subtree) {
                $this->output->writeln(sprintf(
                    '%s- [%s](%s)',
                    str_repeat(' ', $level * 4),
                    $file,
                    $this->normalizePath($subtree[0]->getDetails())
                ));
                $this->renderWithFile($subtree, $level + 1);
            }
        } else {
            usort($tree, new DetailsExtensionComparator());

            /** @var DetailsExtension $detail */
            foreach ($tree as $detail) {
                $this->output->writeln(sprintf(
                    '%s-%s %s *%s / [%s]*',
                    str_repeat(' ', $level * 4),
                    $detail->hasLine() ? " Line {$detail->getLine()} :" : '',
                    $detail->hasMessage() ? $detail->getMessage() : '',
                    $detail->getCategory(),
                    $detail->getInsight()->getTitle()
                ));
            }
        }

        if ($level === 0) {
            $this->output->write(PHP_EOL);
        }
    }

    /**
     * @param array<DetailsExtension> $details
     */
    protected function renderWithoutFile(array $details): void
    {
        if (!$details) {
            return;
        }

        $this->output->writeln('## Other issues');
        $this->output->write(PHP_EOL);

        usort($details, new DetailsExtensionComparator());

        /** @var DetailsExtension $detail */
        foreach ($details as $detail) {
            $this->output->writeln(sprintf(
                '- %s  *%s / [%s]*',
                $detail->getMessage(),
                $detail->getCategory(),
                $detail->getInsight()->getTitle()
            ));
        }
        $this->output->write(PHP_EOL);
    }

    /**
     * @param array<\NunoMaduro\PhpInsights\Domain\Contracts\Insight> $insights
     */
    protected function renderInsightsLinks(array $insights): void
    {
        usort($insights, new InsightComparator());

        foreach ($insights as $insight) {
            $this->output->writeln(sprintf(
                '[%s]: [#] "%s"',
                $insight->getTitle(),
                $insight->getInsightClass()
            ));
        }
    }

    protected function write(): void
    {
        assert($this->output instanceof BufferedOutput);

        file_put_contents($this->filename, $this->output->fetch());
    }
}
