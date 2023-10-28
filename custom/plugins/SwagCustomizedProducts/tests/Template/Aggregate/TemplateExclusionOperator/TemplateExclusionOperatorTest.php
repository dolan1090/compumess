<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Template\Aggregate\TemplateExclusionOperator;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;
use Swag\CustomizedProducts\Template\Aggregate\TemplateExclusion\TemplateExclusionCollection;
use Swag\CustomizedProducts\Template\Aggregate\TemplateExclusion\TemplateExclusionEntity;
use Swag\CustomizedProducts\Template\Aggregate\TemplateExclusionCondition\TemplateExclusionConditionCollection;
use Swag\CustomizedProducts\Template\Aggregate\TemplateExclusionCondition\TemplateExclusionConditionEntity;
use Swag\CustomizedProducts\Template\TemplateEntity;
use Swag\CustomizedProducts\Test\Helper\ServicesTrait;

class TemplateExclusionOperatorTest extends TestCase
{
    use ServicesTrait;

    private readonly EntityRepository $templateRepository;

    public function setUp(): void
    {
        $templateRepository = $this->getContainer()->get('swag_customized_products_template.repository');
        static::assertNotNull($templateRepository);

        $this->templateRepository = $templateRepository;
    }

    public function testAssertOperatorCount(): void
    {
        // First we check if we got the expected count of operators
        $context = Context::createDefaultContext();
        /** @var EntityRepository $templateExclusionOperatorRepository */
        $templateExclusionOperatorRepository = $this->getContainer()->get(
            'swag_customized_products_template_exclusion_operator.repository'
        );
        $ids = $templateExclusionOperatorRepository->searchIds(new Criteria(), $context)->getIds();
        static::assertCount(24, $ids);
        /** @var EntityRepository $templateExclusionOperatorTranslationRepository */
        $templateExclusionOperatorTranslationRepository = $this->getContainer()->get(
            'swag_customized_products_template_exclusion_operator_translation.repository'
        );
        // Second we check that we have exact twice the amount of translations as operators ( Each Operator in English and German )
        $translationIds = $templateExclusionOperatorTranslationRepository->searchIds(
            new Criteria(),
            $context
        )->getIds();
        static::assertCount(2 * \count($ids), $translationIds);
    }

    public function testThatOperatorsCanGetAssignedToACondition(): void
    {
        $context = Context::createDefaultContext();
        $optionId = Uuid::randomHex();
        $templateId = Uuid::randomHex();

        $this->createTemplate(
            $templateId,
            $context,
            [
                'options' => [
                    [
                        'id' => $optionId,
                        'type' => 'checkbox',
                        'displayName' => 'ExampleOption',
                        'position' => 1,
                        'typeProperties' => [],
                    ],
                ],
                'exclusions' => [
                    [
                        'name' => 'firstExclusion',
                        'conditions' => [
                            [
                                'templateOptionId' => $optionId,
                                'templateExclusionOperator' => [
                                    'operator' => 'XXX',
                                    'label' => 'ExampleOperator',
                                    'templateOptionType' => 'checkbox',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $criteria = new Criteria([$templateId]);
        $criteria->addAssociation('exclusions.conditions.templateExclusionOperator');
        $template = $this->templateRepository->search($criteria, $context)->first();

        static::assertInstanceOf(TemplateEntity::class, $template);
        static::assertInstanceOf(TemplateExclusionCollection::class, $template->getExclusions());

        $exclusion = $template->getExclusions()->first();
        static::assertInstanceOf(TemplateExclusionEntity::class, $exclusion);
        static::assertInstanceOf(TemplateExclusionConditionCollection::class, $exclusion->getConditions());

        $conditionEntity = $exclusion->getConditions()->first();
        static::assertInstanceOf(TemplateExclusionConditionEntity::class, $conditionEntity);
        static::assertSame('XXX', $conditionEntity->getTemplateExclusionOperator()->getOperator());
        static::assertSame('ExampleOperator', $conditionEntity->getTemplateExclusionOperator()->getLabel());
        static::assertSame('checkbox', $conditionEntity->getTemplateExclusionOperator()->getTemplateOptionType());
    }
}
