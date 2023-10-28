<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B;

use Shopware\Core\Framework\Log\Package;

#[Package('checkout')]
enum B2bComponents: string
{
    case EMPLOYEE_MANAGEMENT = 'EMPLOYEE_MANAGEMENT';
    case QUICK_ORDER = 'QUICK_ORDER';
}
