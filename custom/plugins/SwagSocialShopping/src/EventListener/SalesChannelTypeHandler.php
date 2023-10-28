<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagSocialShopping\EventListener;

use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntitySearchResultLoadedEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelType\SalesChannelTypeEntity;
use SwagSocialShopping\Component\Network\NetworkInterface;
use SwagSocialShopping\Component\Network\NetworkRegistryInterface;
use SwagSocialShopping\Exception\InvalidNetworkException;
use SwagSocialShopping\SwagSocialShopping;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SalesChannelTypeHandler implements EventSubscriberInterface
{
    private NetworkRegistryInterface $networkRegistry;

    private EntityRepository $salesChannelRepository;

    public function __construct(
        NetworkRegistryInterface $networkRegistry,
        EntityRepository $salesChannelRepository
    ) {
        $this->networkRegistry = $networkRegistry;
        $this->salesChannelRepository = $salesChannelRepository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'sales_channel_type.search.result.loaded' => 'replaceSocialShoppingTypeWithNetworks',
        ];
    }

    public function replaceSocialShoppingTypeWithNetworks(EntitySearchResultLoadedEvent $event): void
    {
        $result = $event->getResult();

        if (!$this->hasStorefrontSalesChannel($event->getContext())) {
            return;
        }

        // Remove the original SalesChannel type, that got replaced
        $result->getEntities()->remove(SwagSocialShopping::SALES_CHANNEL_TYPE_SOCIAL_SHOPPING);

        foreach ($this->networkRegistry->getNetworks() as $network) {
            if (!$network instanceof NetworkInterface) {
                throw new InvalidNetworkException(\get_class($network));
            }

            $salesChannelType = new SalesChannelTypeEntity();
            $salesChannelType->setName(\ucfirst($network->getName()));
            $salesChannelType->setIconName($network->getIconName());
            $salesChannelType->setUniqueIdentifier(
                \sprintf('%s-%s', SwagSocialShopping::SALES_CHANNEL_TYPE_SOCIAL_SHOPPING, $network->getName())
            );

            $salesChannelType->setTranslated([
                'name' => \sprintf('%s.%s', $network->getTranslationKey(), 'name'),
                'description' => \sprintf('%s.%s', $network->getTranslationKey(), 'description'),
                'manufacturer' => \sprintf('%s.%s', $network->getTranslationKey(), 'manufacturer'),
                'descriptionLong' => \sprintf('%s.%s', $network->getTranslationKey(), 'descriptionLong'),
            ]);

            $salesChannelType->setCustomFields(['isSocialShoppingType' => true]);

            $result->add($salesChannelType);
        }
    }

    private function hasStorefrontSalesChannel(Context $context): bool
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('typeId', Defaults::SALES_CHANNEL_TYPE_STOREFRONT));
        $criteria->setTotalCountMode(Criteria::TOTAL_COUNT_MODE_EXACT);

        return $this->salesChannelRepository->searchIds($criteria, $context)->getTotal() > 0;
    }
}
