<?php declare(strict_types=1);

namespace Shopware\Commercial\ClassificationCustomer\Exception;

use Shopware\Core\Framework\HttpException;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[Package('checkout')]
class ClassificationCustomerException extends HttpException
{
    final public const GENERATE_TAGS_CODE = 'TAGS__GENERATE_ERROR';
    final public const CLASSIFY_CUSTOMERS_CODE = 'CUSTOMERS__CLASSIFICATION_ERROR';

    public static function generateTagsError(): ClassificationCustomerException
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::GENERATE_TAGS_CODE,
            'Error when generating tags'
        );
    }

    public static function classifyCustomersError(): ClassificationCustomerException
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::CLASSIFY_CUSTOMERS_CODE,
            'Error when classifying customers'
        );
    }
}
