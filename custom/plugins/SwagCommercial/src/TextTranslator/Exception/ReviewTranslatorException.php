<?php declare(strict_types=1);

namespace Shopware\Commercial\TextTranslator\Exception;

use Shopware\Core\Framework\HttpException;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('inventory')]
class ReviewTranslatorException extends HttpException
{
    public const REVIEW_NOT_FOUND = 'REVIEW_TRANSLATOR__REVIEW_NOT_FOUND';

    public const LOCALE_NOT_FOUND = 'REVIEW_TRANSLATOR__LOCALE_NOT_FOUND';

    public const MISSING_REVIEW_ID = 'REVIEW_TRANSLATOR__MISSING_REVIEW_ID';

    public const MISSING_LANGUAGE_ID = 'REVIEW_TRANSLATOR__MISSING_LANGUAGE_ID';

    public const EMPTY_RESPONSE = 'REVIEW_TRANSLATOR__EMPTY_RESPONSE';

    public static function reviewNotFound(string $reviewId): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::REVIEW_NOT_FOUND,
            'Review with id "{{ id }}" not found',
            ['id' => $reviewId]
        );
    }

    public static function localeNotFound(string $localeId): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::LOCALE_NOT_FOUND,
            'Locale for language with id "{{ id }}" not found',
            ['id' => $localeId]
        );
    }

    public static function missingReviewId(): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::MISSING_REVIEW_ID,
            'Review id is missing'
        );
    }

    public static function missingLanguageId(): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::MISSING_LANGUAGE_ID,
            'Language id is missing'
        );
    }

    public static function emptyResponse(): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::EMPTY_RESPONSE,
            'Empty response from translation service'
        );
    }
}
