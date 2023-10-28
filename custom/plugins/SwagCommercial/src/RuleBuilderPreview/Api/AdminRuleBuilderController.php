<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shopware\Commercial\RuleBuilderPreview\Api;

use Shopware\Commercial\RuleBuilderPreview\Domain\Rule\Preview\RulePreview;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Log\Package;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @internal
 */
#[Package('business-ops')]
#[Route(defaults: ['_routeScope' => ['administration']])]
final class AdminRuleBuilderController extends AbstractController
{
    public function __construct(
        private readonly RulePreview $rulePreview,
        private readonly EntityRepository $orderRepository
    ) {
    }

    #[Route(
        path: '/api/_admin/rule-builder-preview/{orderId}',
        name: 'commercial.api.admin.rule-builder-preview',
        defaults: ['_acl' => ['order:read']],
        methods: ['POST'],
        condition: 'service(\'license\').check(\'RULE_BUILDER-6864922\')'
    )]
    public function ruleBuilderPreview(string $orderId, Request $request, Context $context): JsonResponse
    {
        $criteria = new Criteria([$orderId]);
        $criteria->setLimit(1);
        $criteria->addAssociation('transactions');
        $criteria->addAssociation('lineItems');
        $criteria->addAssociation('deliveries');
        /** @var OrderEntity $order */
        $order = $this->orderRepository->search($criteria, $context)->first();

        /** @var array<int, array<string, mixed>> $conditions */
        $conditions = (array) $request->get('conditions');
        $dateTime = $request->get('dateTime');
        $dateTime = (\is_string($dateTime) && \strtotime($dateTime)) ? new \DateTimeImmutable($dateTime) : null;

        $result = $this->rulePreview->preview($order, $conditions, $context, $dateTime)->getFlat();

        return new JsonResponse($result);
    }
}
