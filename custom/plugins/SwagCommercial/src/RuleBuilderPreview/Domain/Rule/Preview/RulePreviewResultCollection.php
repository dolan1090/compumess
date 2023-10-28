<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shopware\Commercial\RuleBuilderPreview\Domain\Rule\Preview;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Struct\Collection;

/**
 * @internal
 *
 * @extends Collection<RulePreviewResult>
 */
#[Package('business-ops')]
final class RulePreviewResultCollection extends Collection
{
    /**
     * @return RulePreviewResult[]
     */
    public function getFlat(): array
    {
        return $this->buildFlat($this);
    }

    protected function getExpectedClass(): string
    {
        return RulePreviewResult::class;
    }

    /**
     * @return RulePreviewResult[]
     */
    private function buildFlat(RulePreviewResultCollection $rulePreviewResults): array
    {
        $flat = [];
        foreach ($rulePreviewResults->getElements() as $rulePreviewResult) {
            foreach ($this->buildFlat($rulePreviewResult->getRules()) as $nest) {
                $nest->setRules(new RulePreviewResultCollection());
                $flat[] = $nest;
            }

            $rulePreviewResult->setRules(new RulePreviewResultCollection());
            $flat[] = $rulePreviewResult;
        }

        return $flat;
    }
}
