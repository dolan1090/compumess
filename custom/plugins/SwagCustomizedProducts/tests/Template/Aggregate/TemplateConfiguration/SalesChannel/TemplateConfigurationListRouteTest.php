<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Template\Aggregate\TemplateConfiguration\SalesChannel;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Test\TestCaseBase\SalesChannelApiTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use Swag\CustomizedProducts\Core\Checkout\CustomizedProductsCartDataCollector;
use Swag\CustomizedProducts\Template\Aggregate\TemplateConfiguration\Service\TemplateConfigurationService;
use Swag\CustomizedProducts\Template\Aggregate\TemplateConfiguration\TemplateConfigurationEntity;
use Swag\CustomizedProducts\Test\Helper\ServicesTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TemplateConfigurationListRouteTest extends TestCase
{
    use SalesChannelApiTestBehaviour;
    use ServicesTrait;

    public function testRoute(): void
    {
        $templateConfig = $this->prepareTemplateConfiguration();

        $browser = $this->getSalesChannelBrowser();
        $browser->request(
            Request::METHOD_GET,
            '/store-api/swag_customized_products_template_configuration'
        );
        $response = $browser->getResponse();

        static::assertSame(Response::HTTP_OK, $response->getStatusCode());
        $content = $response->getContent();
        static::assertNotFalse($content);
        $data = \json_decode($content, true);

        static::assertArrayHasKey('elements', $data);
        $firstTemplate = $data['elements'][0];
        static::assertSame($templateConfig->getId(), $firstTemplate['id']);
    }

    protected function prepareTemplateConfiguration(): TemplateConfigurationEntity
    {
        $context = Context::createDefaultContext();
        $templateId = Uuid::randomHex();
        $this->createTemplate($templateId, $context);

        $templateLineItem = $this->createLineItem($templateId);

        /** @var TemplateConfigurationService $configService */
        $configService = $this->getContainer()->get(TemplateConfigurationService::class);
        $templateConfig = $configService->getTemplateConfiguration($templateLineItem, 'test', $context);

        static::assertNotNull($templateConfig);

        return $templateConfig;
    }

    protected function createLineItem(string $templateId): LineItem
    {
        $templateLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            $templateId
        );
        $templateLineItem->setPayloadValue(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_CONFIGURATION_HASH,
            Uuid::randomHex()
        );

        return $templateLineItem;
    }
}
