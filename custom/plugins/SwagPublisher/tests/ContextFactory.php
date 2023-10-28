<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisherTest;

use Shopware\Core\Framework\Api\Context\AdminApiSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Symfony\Component\DependencyInjection\ContainerInterface;

trait ContextFactory
{
    abstract protected static function getContainer(): ContainerInterface;

    private function createAdminApiSourceContext(?string $userId = null): Context
    {
        if (!$userId) {
            $userId = $this->getContainer()
                    ->get('user.repository')
                    ->search(new Criteria(), Context::createDefaultContext())
                    ->first()
                    ->getId();
        }

        $source = new AdminApiSource($userId);
        $source->setIsAdmin(true);

        return Context::createDefaultContext($source);
    }
}
