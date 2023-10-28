<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Storefront\Upload\Exception;

use Shopware\Core\Framework\ShopwareHttpException;
use Symfony\Component\HttpFoundation\Response;

class SwagCustomizedProductsMaximumFileCountExceededException extends ShopwareHttpException
{
    protected const KEY = 'customizedProducts.addToCart.error.maxFileCountExceeded';

    public function __construct(
        string $message = 'Too many files',
        array $parameters = [],
        ?\Throwable $e = null
    ) {
        parent::__construct($message, $parameters, $e);
    }

    public function getErrorCode(): string
    {
        return self::KEY;
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_UNPROCESSABLE_ENTITY;
    }
}
