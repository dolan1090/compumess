<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\PayPal\Checkout\SalesChannel;

use Shopware\Core\Checkout\Cart\Exception\CustomerNotLoggedInException;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Swag\PayPal\Checkout\TokenResponse;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractCreateOrderRoute
{
    abstract public function getDecorated(): AbstractCreateOrderRoute;

    /**
     * @throws CustomerNotLoggedInException
     */
    abstract public function createPayPalOrder(SalesChannelContext $salesChannelContext, Request $request): TokenResponse;
}
