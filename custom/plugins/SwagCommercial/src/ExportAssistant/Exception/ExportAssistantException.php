<?php declare(strict_types=1);

namespace Shopware\Commercial\ExportAssistant\Exception;

use Shopware\Core\Framework\HttpException;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

/**
 * @final
 *
 * @internal
 */
#[Package('system-settings')]
class ExportAssistantException extends HttpException
{
    final public const EXPORT_ASSISTANT__DETECT_ENTITY_CODE = 'EXPORT_ASSISTANT__DETECT_ENTITY_ERROR';
    final public const EXPORT_ASSISTANT__GENERATE_CRITERIA_CODE = 'EXPORT_ASSISTANT__GENERATE_CRITERIA_ERROR';

    public static function cannotDetectEntity(?string $prompt = null): ExportAssistantException
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::EXPORT_ASSISTANT__DETECT_ENTITY_CODE,
            sprintf('Cannot detect entity from the prompt: "%s"', $prompt)
        );
    }

    public static function cannotGenerateCriteria(?string $reason = null, ?string $prompt = null): ExportAssistantException
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::EXPORT_ASSISTANT__GENERATE_CRITERIA_CODE,
            sprintf('Unable to generate request from the prompt: "%s"', $prompt),
            $reason ? ['reason' => $reason] : []
        );
    }
}
