<?php declare(strict_types=1);

namespace Shopware\Commercial\ReturnManagement\Domain\Validation;

use Shopware\Commercial\ReturnManagement\Domain\StateHandler\PositionStateHandler;
use Shopware\Commercial\ReturnManagement\Entity\OrderReturn\OrderReturnDefinition;
use Shopware\Commercial\ReturnManagement\Entity\OrderReturnLineItem\OrderReturnLineItemAllowedTypes;
use Shopware\Commercial\ReturnManagement\Entity\OrderReturnLineItemReason\OrderReturnLineItemReasonDefinition;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemDefinition;
use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Validation\EntityExists;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Validation\DataValidationDefinition;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintViolation;

#[Package('checkout')]
class OrderReturnValidationFactory extends AbstractOrderReturnValidationFactory
{
    public function create(string $orderId, SalesChannelContext $context): DataValidationDefinition
    {
        $definition = new DataValidationDefinition('order.return.created');

        $this->addConstraintsCreate($definition, $orderId, $context);

        return $definition;
    }

    public function addItems(string $orderId, Context $context): DataValidationDefinition
    {
        $definition = new DataValidationDefinition('order.return.add-items');

        $this->addConstraintsAddItems($definition, $orderId, $context);

        return $definition;
    }

    /**
     * @param int|string|null $value
     *
     * @internal
     */
    public function buildConstraintViolation(string $messageTemplate, string $propertyPath, $value = null, ?string $errorCode = self::ERROR_CODE_GENERAL): ConstraintViolation
    {
        $parameters = ['{{ value }}' => $value];

        return new ConstraintViolation(
            str_replace(array_keys($parameters), $parameters, $messageTemplate),
            $messageTemplate,
            $parameters,
            null,
            $propertyPath,
            $value,
            null,
            $errorCode
        );
    }

    private function addConstraintsCreate(DataValidationDefinition $definition, string $orderId, SalesChannelContext $context): void
    {
        $definition->add('comment', new NotBlank(['allowNull' => true]), new Type('string'));
        $definition->add('lineItems', new NotBlank());
        $definition->addList(
            'lineItems',
            (new DataValidationDefinition())
                ->add(
                    'orderLineItemId',
                    new EntityExists([
                        'entity' => OrderLineItemDefinition::ENTITY_NAME,
                        'context' => $context->getContext(),
                        'criteria' => (new Criteria())
                            ->addFilter(
                                new EqualsFilter('orderId', $orderId),
                                new EqualsAnyFilter('type', OrderReturnLineItemAllowedTypes::LINE_ITEM_TYPES),
                                new MultiFilter(MultiFilter::CONNECTION_OR, [
                                    new EqualsFilter('state.technicalName', null),
                                    new NotFilter(
                                        MultiFilter::CONNECTION_AND,
                                        [
                                            new EqualsAnyFilter('state.technicalName', [
                                                PositionStateHandler::STATE_CANCELLED,
                                                PositionStateHandler::STATE_RETURNED,
                                                PositionStateHandler::STATE_RETURNED_PARTIALLY,
                                            ]),
                                        ]
                                    ),
                                ]),
                            ),
                    ]),
                )
                ->add('quantity', new NotBlank(), new Type('numeric'))
                ->add(
                    'reasonId',
                    new NotBlank(['allowNull' => true]),
                    new EntityExists(['entity' => OrderReturnLineItemReasonDefinition::ENTITY_NAME, 'context' => $context->getContext()])
                )
                ->add('refundAmount', new NotBlank(['allowNull' => true]), new Type('float'))
                ->add('restockQuantity', new NotBlank(['allowNull' => true]), new Type('numeric'))
                ->add('internalComment', new NotBlank(['allowNull' => true]), new Type('string'))
        );
    }

    private function addConstraintsAddItems(DataValidationDefinition $definition, string $orderId, Context $context): void
    {
        $definition->add(
            'orderReturnId',
            new EntityExists([
                'entity' => OrderReturnDefinition::ENTITY_NAME,
                'context' => $context,
                'criteria' => (new Criteria())->addFilter(new EqualsFilter('orderId', $orderId)),
            ]),
        );
        $definition->add(
            'orderId',
            new NotBlank(),
            new EntityExists(['entity' => OrderDefinition::ENTITY_NAME, 'context' => $context])
        );
        $definition->add('orderLineItems', new NotBlank());
        $definition->addList(
            'orderLineItems',
            (new DataValidationDefinition())
                ->add(
                    'orderLineItemId',
                    new EntityExists([
                        'entity' => OrderLineItemDefinition::ENTITY_NAME,
                        'context' => $context,
                        'criteria' => (new Criteria())
                            ->addFilter(
                                new EqualsFilter('orderId', $orderId),
                                new EqualsAnyFilter('type', OrderReturnLineItemAllowedTypes::LINE_ITEM_TYPES),
                                new MultiFilter(MultiFilter::CONNECTION_OR, [
                                    new EqualsFilter('state.technicalName', null),
                                    new NotFilter(
                                        MultiFilter::CONNECTION_AND,
                                        [
                                            new EqualsAnyFilter('state.technicalName', [
                                                PositionStateHandler::STATE_CANCELLED,
                                                PositionStateHandler::STATE_RETURNED,
                                                PositionStateHandler::STATE_RETURNED_PARTIALLY,
                                            ]),
                                        ]
                                    ),
                                ]),
                            ),
                    ])
                )
                ->add('quantity', new NotBlank(), new Type('numeric'))
                ->add('internalComment', new NotBlank(['allowNull' => true]), new Type('string'))
        );
    }
}
