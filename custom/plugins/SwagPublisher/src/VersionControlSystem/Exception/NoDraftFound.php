<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisher\VersionControlSystem\Exception;

use Shopware\Core\Framework\ShopwareHttpException;
use SwagPublisher\Common\PublisherException;
use Symfony\Component\HttpFoundation\Response;

class NoDraftFound extends ShopwareHttpException implements PublisherException
{
    public function __construct()
    {
        parent::__construct('Not a single draft found with that particular version');
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_NOT_FOUND;
    }

    public function getErrorCode(): string
    {
        return self::EXCEPTION_CODE_PREFIX . 'NO_DRAFT_FOUND';
    }
}
