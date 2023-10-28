<?php declare(strict_types=1);

namespace Shopware\Commercial\Licensing;

use Shopware\Commercial\Licensing\Exception\LicenseExpiredException;
use Shopware\Core\Framework\Log\Package;
use Symfony\Bundle\FrameworkBundle\Routing\Attribute\AsRoutingConditionService;

#[Package('merchant-services')]
#[AsRoutingConditionService(alias: 'license')]
class LicenseRouteChecker
{
    public function check(string $toggle): bool
    {
        if (License::get($toggle)) {
            return true;
        }

        throw new LicenseExpiredException();
    }
}
