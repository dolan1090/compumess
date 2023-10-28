<?php declare(strict_types=1);

namespace Shopware\Commercial\ContentGenerator\Exception;

use Shopware\Core\Framework\HttpException;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[Package('content')]
class ContentGeneratorException extends HttpException
{
    final public const CONTENT_CREATOR_GENERATE_CODE = 'CONTENT_CREATOR__GENERATE_ERROR';
    final public const CONTENT_CREATOR_EDIT_CODE = 'CONTENT_CREATOR__EDIT_ERROR';

    public static function contentGenerateError(): ContentGeneratorException
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::CONTENT_CREATOR_GENERATE_CODE,
            'Error when generating content'
        );
    }

    public static function contentEditError(): ContentGeneratorException
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::CONTENT_CREATOR_EDIT_CODE,
            'Error when edit content'
        );
    }
}
