<?php declare(strict_types=1);

namespace Shopware\Commercial\FlowBuilder\FlowSharing\Exception;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\ShopwareHttpException;
use Symfony\Component\HttpFoundation\Response;

#[Package('business-ops')]
class InvalidFlowException extends ShopwareHttpException
{
    public function __construct(string $flowId)
    {
        $message = sprintf('The flow with id "%s" is invalid or could not be found.', $flowId);
        parent::__construct($message);
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }

    public function getErrorCode(): string
    {
        return 'FLOW__INVALID_FLOW_ID';
    }
}
