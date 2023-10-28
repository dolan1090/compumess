<?php declare(strict_types=1);

namespace Shopware\Commercial\ReturnManagement\Domain\Validation;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Validation\DataValidationDefinition;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\Validator\ConstraintViolation;

#[Package('checkout')]
abstract class AbstractOrderReturnValidationFactory
{
    final public const ERROR_CODE_GENERAL = 'CONTENT__INVALID_RETURN_LINE_ITEM';
    final public const ERROR_CODE_INVALID_RETURN_LINE_ITEM_STATE = 'CONTENT__INVALID_RETURN_LINE_ITEM_STATE';
    final public const ERROR_CODE_INVALID_RETURN_LINE_ITEM_QUANTITY = 'CONTENT__INVALID_RETURN_LINE_ITEM_QUANTITY';
    final public const ERROR_CODE_DUPLICATE_ORDER_RETURN_LINE_ITEM = 'CONTENT__DUPLICATE_ORDER_RETURN_LINE_ITEM';

    abstract public function create(string $orderId, SalesChannelContext $context): DataValidationDefinition;

    abstract public function addItems(string $orderId, Context $context): DataValidationDefinition;

    /**
     * @param int|string|null $value
     *
     * @internal
     */
    abstract public function buildConstraintViolation(string $messageTemplate, string $propertyPath, $value = null, ?string $errorCode = self::ERROR_CODE_GENERAL): ConstraintViolation;
}
