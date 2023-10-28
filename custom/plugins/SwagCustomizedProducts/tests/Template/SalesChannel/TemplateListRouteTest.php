<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Template\SalesChannel;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Test\TestCaseBase\SalesChannelApiTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use Swag\CustomizedProducts\Test\Helper\ServicesTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TemplateListRouteTest extends TestCase
{
    use SalesChannelApiTestBehaviour;
    use ServicesTrait;

    public function testRoute(): void
    {
        $templateId = Uuid::randomHex();
        $this->createTemplate($templateId, Context::createDefaultContext());

        $browser = $this->getSalesChannelBrowser();
        $browser->request(
            Request::METHOD_GET,
            '/store-api/swag_customized_products_template'
        );
        $response = $browser->getResponse();

        static::assertSame(Response::HTTP_OK, $response->getStatusCode());
        $content = $response->getContent();
        static::assertNotFalse($content);
        $data = \json_decode($content, true);

        static::assertArrayHasKey('elements', $data);
        $firstTemplate = $data['elements'][0];
        static::assertSame($templateId, $firstTemplate['id']);
    }
}
