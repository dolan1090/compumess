<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Domain\Validation;

use Shopware\Commercial\B2B\EmployeeManagement\Exception\EmployeeManagementException;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainCollection;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * @internal
 */
#[Package('checkout')]
class UrlValidator
{
    public static function validateStorefrontUrl(?string $url, SalesChannelContext $context): void
    {
        /** @var SalesChannelDomainCollection $salesChannelDomainCollection */
        $salesChannelDomainCollection = $context->getSalesChannel()->getDomains();

        if (!$salesChannelDomainCollection) {
            throw EmployeeManagementException::invalidRequestArgument('Invalid storefrontUrl provided');
        }

        $urls = array_map(
            static fn (SalesChannelDomainEntity $domainEntity) => rtrim(
                $domainEntity->getUrl(),
                '/'
            ),
            $salesChannelDomainCollection->getElements()
        );

        if (\in_array($url, $urls, true)) {
            return;
        }

        throw EmployeeManagementException::invalidRequestArgument('Invalid storefrontUrl provided');
    }
}
