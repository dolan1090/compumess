<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Template\Aggregate\TemplateOptionValue\SalesChannel;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Test\TestCaseBase\SalesChannelApiTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use Swag\CustomizedProducts\Test\Helper\ServicesTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TemplateOptionValueListRouteTest extends TestCase
{
    use SalesChannelApiTestBehaviour;
    use ServicesTrait;

    public function testRoute(): void
    {
        $valueId = Uuid::randomHex();

        $this->createTemplate(
            Uuid::randomHex(),
            Context::createDefaultContext(),
            [
                'options' => [
                    $this->getTemplateOptionsArrayForCreation(
                        [
                            'values' => [
                                $this->getTemplateOptionValuesArrayForCreation(['id' => $valueId, 'price' => null]),
                            ],
                        ]
                    ),
                ],
            ]
        );

        $browser = $this->getSalesChannelBrowser();
        $browser->request(
            Request::METHOD_GET,
            '/store-api/swag_customized_products_template_option_value'
        );
        $response = $browser->getResponse();

        static::assertSame(Response::HTTP_OK, $response->getStatusCode());
        $content = $response->getContent();
        static::assertNotFalse($content);
        $data = \json_decode($content, true);

        static::assertArrayHasKey('elements', $data);
        $firstTemplate = $data['elements'][0];
        static::assertSame($valueId, $firstTemplate['id']);
    }
}
