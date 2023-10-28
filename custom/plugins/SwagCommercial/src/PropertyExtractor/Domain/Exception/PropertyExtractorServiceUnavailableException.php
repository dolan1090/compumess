<?php declare(strict_types=1);

namespace Shopware\Commercial\PropertyExtractor\Domain\Exception;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\ShopwareHttpException;
use Symfony\Component\HttpFoundation\Response;

#[Package('business-ops')]
class PropertyExtractorServiceUnavailableException extends ShopwareHttpException
{
    public function __construct()
    {
        parent::__construct('The service is currently not available. Please try again later.');
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }

    public function getErrorCode(): string
    {
        return 'PROPERTY_EXTRACTOR__SERVICE_UNAVAILABLE';
    }
}
