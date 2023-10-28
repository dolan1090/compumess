<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement;

use Shopware\Commercial\B2B\CommercialB2BBundle;
use Shopware\Core\Framework\Log\Package;

/**
 * @internal
 *
 * @phpstan-import-type Feature from CommercialB2BBundle
 */
#[Package('checkout')]
class EmployeeManagement extends CommercialB2BBundle
{
    public const FEATURE_CODE = 'EMPLOYEE_MANAGEMENT';

    /**
     * @return list<Feature>
     */
    public function describeFeatures(): array
    {
        return [
            [
                'code' => self::FEATURE_CODE,
                'name' => 'Employee Management',
                'description' => 'Your customers can create employee accounts with roles and permissions to realise individual order processes.',
                'type' => self::TYPE_B2B,
            ],
        ];
    }
}
