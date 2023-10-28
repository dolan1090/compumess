<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Template\Api;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Test\TestCaseBase\AdminApiTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use Swag\CustomizedProducts\Template\Aggregate\TemplateExclusionOperator\TemplateExclusionOperatorDefinition;
use Swag\CustomizedProducts\Test\Helper\ServicesTrait;
use Symfony\Component\HttpFoundation\Response;

class TemplateControllerTest extends TestCase
{
    use AdminApiTestBehaviour;
    use ServicesTrait;

    public function testAddTreeGenerationMessageToQueue(): void
    {
        $templateId = Uuid::randomHex();
        $firstOptionId = Uuid::randomHex();
        $secondOptionId = Uuid::randomHex();
        /** @var EntityRepository $operatorRepository */
        $operatorRepository = $this->getContainer()->get(TemplateExclusionOperatorDefinition::ENTITY_NAME . '.repository');
        $this->createTemplate(
            $templateId,
            Context::createDefaultContext(),
            [
                'options' => [
                    [
                        'id' => $firstOptionId,
                        'type' => 'checkbox',
                        'typeProperties' => [],
                        'displayName' => 'Checkbox1',
                        'position' => 1,
                    ],
                    [
                        'id' => $secondOptionId,
                        'type' => 'checkbox',
                        'typeProperties' => [],
                        'displayName' => 'Checkbox2',
                        'position' => 2,
                    ],
                ],
                'exclusions' => [
                    [
                        'name' => 'firstEverTemplateExclusion',
                        'conditions' => [
                            [
                                'templateOptionId' => $firstOptionId,
                                'templateExclusionOperatorId' => $this->getOperatorIdForType($operatorRepository),
                            ],
                            [
                                'templateOptionId' => $secondOptionId,
                                'templateExclusionOperatorId' => $this->getOperatorIdForType($operatorRepository),
                            ],
                        ],
                    ],
                ],
            ]
        );

        $this->getBrowser()->request(
            'POST',
            \sprintf('/api/_action/swag-customized-products-template/%s/tree', $templateId)
        );

        static::assertSame(
            Response::HTTP_NO_CONTENT,
            $this->getBrowser()->getResponse()->getStatusCode(),
            (string) $this->getBrowser()->getResponse()->getContent()
        );
    }
}
