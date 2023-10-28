<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shopware\Commercial\RuleBuilderPreview\Command;

use Shopware\Commercial\Licensing\Exception\LicenseExpiredException;
use Shopware\Commercial\Licensing\License;
use Shopware\Commercial\RuleBuilderPreview\Domain\Rule\Preview\RulePreview;
use Shopware\Commercial\RuleBuilderPreview\Domain\Rule\Preview\RulePreviewResultCollection;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Content\Rule\Aggregate\RuleCondition\RuleConditionEntity;
use Shopware\Core\Content\Rule\RuleEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @internal
 */
#[Package('business-ops')]
#[AsCommand('rule:preview')]
final class RulePreviewCommand extends Command
{
    public function __construct(
        private readonly RulePreview $rulePreview,
        private readonly EntityRepository $orderRepository,
        private readonly EntityRepository $ruleRepository,
        private readonly EntityRepository $conditionRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('orderNumber', InputArgument::REQUIRED, 'Order number to preview rule with')
            ->addArgument('ruleId', InputArgument::OPTIONAL, 'Rule ID to preview')
            ->addOption(
                'date-time',
                'd',
                InputOption::VALUE_OPTIONAL,
                'Date and time to evaluate the rule at (DEFAULT: now)'
            )
            ->addOption(
                'skip-mock',
                's',
                InputOption::VALUE_NONE,
                'Skip evaluation of rules if not necessary'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!License::get('RULE_BUILDER-1967308')) {
            throw new LicenseExpiredException();
        }

        $io = new SymfonyStyle($input, $output);
        $orderNumber = $input->getArgument('orderNumber');
        $orderNumber = \is_string($orderNumber) ? $orderNumber : '';
        $ruleId = $input->getArgument('ruleId');
        $dateTime = $input->getOption('date-time');
        $dateTime = (\is_string($dateTime) && \strtotime($dateTime)) ? new \DateTimeImmutable($dateTime) : null;
        $skipMock = (bool) $input->getOption('skip-mock');

        $context = Context::createDefaultContext();
        $criteria = new Criteria();
        $criteria->setLimit(1);
        $criteria->addAssociation('transactions');
        $criteria->addAssociation('lineItems');
        $criteria->addAssociation('deliveries');
        $criteria->addFilter(new EqualsFilter('orderNumber', $orderNumber));

        $order = $this->orderRepository->search($criteria, $context)->first();

        if (!$order instanceof OrderEntity) {
            return self::FAILURE;
        }

        $ruleId = \is_string($ruleId) ? $ruleId : $this->chooseRule($context, $io)->getId();
        $conditions = $this->getConditions($context, $ruleId);

        $results = $this->rulePreview->preview($order, $conditions, $context, $dateTime, $skipMock);
        $rootResult = $results->first();

        if (!$rootResult) {
            return self::FAILURE;
        }

        $io->success(\sprintf(
            'Rule with ID "%s" evaluated to %s with order number %s',
            $ruleId,
            $rootResult->isMatch() ? 'true' : 'false',
            $orderNumber
        ));

        $this->printResultTree($io, $results);

        $io->newLine();

        return self::SUCCESS;
    }

    /**
     * @param bool[] $register
     */
    private function printResultTree(
        SymfonyStyle $io,
        RulePreviewResultCollection $results,
        int $level = 0,
        array $register = []
    ): void {
        $register[$level] = true;
        $results = $results->getElements();

        foreach ($results as $key => $result) {
            $isLast = !isset($results[((int) $key) + 1]);

            $linePrefix = '';
            $spacer = '';
            foreach (\array_keys($register) as $column) {
                if ($column === $level) {
                    $spacer = $linePrefix . '│  ';
                    $linePrefix .= $isLast ? '└─ ' : '├─ ';

                    continue;
                }

                $linePrefix .= $register[$column] ? '│  ' : '   ';
            }

            $io->writeln($spacer);

            $lineItem = $result->getLineItem();
            $matchedLineItem = '';

            if ($lineItem) {
                $matchedLineItem = \sprintf(' => <fg=%s>%s</>', $result->isMatch() ? 'green' : 'red', $lineItem->getLabel() ?? '');
            }

            $io->writeln(\sprintf(
                '%s%s <fg=yellow>%s</> %s%s',
                $linePrefix,
                $result->isMatch() ? '<fg=green>true</>' : '<fg=red>false</>',
                $result->getName(),
                $this->formatParameters($result->getParameters()),
                $matchedLineItem
            ));

            $register[$level] = !$isLast;

            $this->printResultTree($io, $result->getRules(), $level + 1, $register);
        }
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function formatParameters(array $parameters): string
    {
        if (empty($parameters)) {
            return '';
        }

        $parameterStrings = [];

        foreach ($parameters as $key => $value) {
            if (\is_array($value)) {
                $value = \json_encode($value, \JSON_THROW_ON_ERROR);
            }
            if (\is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }
            if ($value instanceof \DateTime) {
                $value = $value->format(Defaults::STORAGE_DATE_TIME_FORMAT);
            }
            if (\is_string($value) || \is_numeric($value)) {
                $parameterStrings[] = \sprintf('<fg=magenta>%s</>: %s', $key, $value);
            }
        }

        return \sprintf('<fg=gray>[</> %s <fg=gray>]</>', \implode(' <fg=gray>|</> ', $parameterStrings));
    }

    private function chooseRule(Context $context, SymfonyStyle $io, int $page = 1): RuleEntity
    {
        $criteria = new Criteria();
        $criteria->setLimit(8);
        $criteria->setOffset(($page - 1) * $criteria->getLimit());
        $criteria->setTotalCountMode(1);
        $result = $this->ruleRepository->search($criteria, $context);

        /** @var RuleEntity[] $byName */
        $byName = [];
        /** @var RuleEntity $rule */
        foreach ($result->getEntities() as $rule) {
            $byName[$rule->getName()] = $rule;
        }

        $hasMoreResults = $result->getTotal() > $page * $criteria->getLimit();
        $validAnswers = \array_keys($byName);
        $showMoreLabel = '<fg=magenta>show more...</>';

        if ($hasMoreResults) {
            $validAnswers[] = $showMoreLabel;
        }

        $answer = $io->choice('Please choose a rule', $validAnswers);

        if ($answer === $showMoreLabel && $hasMoreResults) {
            return $this->chooseRule($context, $io, $page + 1);
        }

        return $byName[$answer];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getConditions(Context $context, string $ruleId): array
    {
        $criteria = new Criteria();
        $criteria->setLimit(500);
        $criteria->addFilter(new EqualsFilter('ruleId', $ruleId));

        /** @var RuleConditionEntity[] $conditions */
        $conditions = $this->conditionRepository->search($criteria, $context)->getElements();

        return $this->buildNested($conditions);
    }

    /**
     * @param RuleConditionEntity[] $conditions
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildNested(array $conditions, ?string $parentId = null): array
    {
        $nested = [];

        foreach ($conditions as $condition) {
            if ($condition->getParentId() !== $parentId) {
                continue;
            }

            $condition = $condition->jsonSerialize();
            $parentId = \is_string($condition['id']) ? $condition['id'] : null;
            $condition['children'] = $this->buildNested($conditions, $parentId);

            $nested[] = $condition;
        }

        return $nested;
    }
}
