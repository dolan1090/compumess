<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagSocialShopping\Api;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Routing\Exception\MissingRequestParameterException;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use SwagSocialShopping\Component\DataFeed\DataFeedHandler;
use SwagSocialShopping\Component\MessageQueue\SocialShoppingValidation;
use SwagSocialShopping\Component\Network\NetworkInterface;
use SwagSocialShopping\Component\Network\NetworkRegistryInterface;
use SwagSocialShopping\Component\Validation\NetworkProductValidator;
use SwagSocialShopping\DataAbstractionLayer\Entity\SocialShoppingSalesChannelEntity;
use SwagSocialShopping\Exception\SocialShoppingSalesChannelNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['api']])]
class SocialShoppingController extends AbstractController
{
    private NetworkRegistryInterface $networkRegistry;

    private MessageBusInterface $messageBus;

    private EntityRepository $socialShoppingSalesChannelRepository;

    private NetworkProductValidator $networkProductValidator;

    private DataFeedHandler $dataFeedHandler;

    public function __construct(
        NetworkRegistryInterface $networkRegistry,
        MessageBusInterface $messageBus,
        EntityRepository $socialShoppingSalesChannelRepository,
        NetworkProductValidator $networkProductValidator,
        DataFeedHandler $dataFeedHandler
    ) {
        $this->networkRegistry = $networkRegistry;
        $this->messageBus = $messageBus;
        $this->socialShoppingSalesChannelRepository = $socialShoppingSalesChannelRepository;
        $this->networkProductValidator = $networkProductValidator;
        $this->dataFeedHandler = $dataFeedHandler;
    }

    #[Route(path: '/api/_action/social-shopping/networks', name: 'api.action.social_shopping.networks', defaults: ['_acl' => ['sales_channel.viewer']], methods: ['GET'])]
    public function getNetworks(): JsonResponse
    {
        $networks = [];

        foreach ($this->networkRegistry->getNetworks() as $network) {
            if (!($network instanceof NetworkInterface)) {
                continue;
            }

            $networks[$network->getName()] = \get_class($network);
        }

        return new JsonResponse($networks);
    }

    #[Route(path: '/api/_action/social-shopping/validate', name: 'api.action.social_shopping.validate', defaults: ['_acl' => ['sales_channel.viewer']], methods: ['POST'])]
    public function validate(RequestDataBag $dataBag, Context $context): Response
    {
        $id = $dataBag->get('social_shopping_sales_channel_id');

        if (!$id) {
            throw new MissingRequestParameterException('social_shopping_sales_channel_id');
        }

        $socialShoppingSalesChannel = $this->socialShoppingSalesChannelRepository->search(
            new Criteria([$id]),
            $context
        )->get($id);

        if (!$socialShoppingSalesChannel instanceof SocialShoppingSalesChannelEntity) {
            throw new SocialShoppingSalesChannelNotFoundException((string) $id);
        }

        $this->networkProductValidator->clearErrors($socialShoppingSalesChannel->getSalesChannelId(), $context);

        $this->setValidating($id, $context);

        $this->messageBus->dispatch(
            new SocialShoppingValidation($id)
        );

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    #[Route(path: '/api/_action/social-shopping/reset', name: 'api.action.social_shopping.reset', defaults: ['_acl' => ['sales_channel.viewer']], methods: ['POST'])]
    public function reset(RequestDataBag $dataBag, Context $context): Response
    {
        $socialShoppingSalesChannelId = $dataBag->get('social_shopping_sales_channel_id');
        if ($socialShoppingSalesChannelId === null) {
            throw new MissingRequestParameterException('social_shopping_sales_channel_id');
        }

        $socialShoppingSalesChannel = $this->socialShoppingSalesChannelRepository->search(
            new Criteria([$socialShoppingSalesChannelId]),
            $context
        )->get($socialShoppingSalesChannelId);

        if (!$socialShoppingSalesChannel instanceof SocialShoppingSalesChannelEntity) {
            throw new SocialShoppingSalesChannelNotFoundException((string) $socialShoppingSalesChannelId);
        }

        $this->dataFeedHandler->createDataFeedForSalesChannelId($socialShoppingSalesChannelId, $context);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    private function setValidating(string $socialShoppingSalesChannelId, Context $context): void
    {
        $this->socialShoppingSalesChannelRepository->update(
            [
                [
                    'id' => $socialShoppingSalesChannelId,
                    'isValidating' => true,
                ],
            ],
            $context
        );
    }
}
