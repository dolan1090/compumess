<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisherTest;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionValidator;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Storefront\Test\Controller\StorefrontControllerTestBehaviour;

class ShopwareLivelinessTest extends TestCase
{
    use IntegrationTestBehaviour;
    use StorefrontControllerTestBehaviour;

    public function testIndexStillReachable(): void
    {
        $response = $this->request('GET', '', []);

        static::assertTrue($response->isSuccessful());
    }

    public function testDalDefinitionsStillValid(): void
    {
        $validator = $this->getContainer()->get(DefinitionValidator::class);
        $violations = \array_filter($validator->validate(), 'count');

        static::assertCount(0, $violations, \print_r($violations, true));
    }
}
