<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Entity\BusinessPartner;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<BusinessPartnerEntity>
 */
#[Package('checkout')]
class BusinessPartnerCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'business_partner_collection';
    }

    protected function getExpectedClass(): string
    {
        return BusinessPartnerEntity::class;
    }
}
