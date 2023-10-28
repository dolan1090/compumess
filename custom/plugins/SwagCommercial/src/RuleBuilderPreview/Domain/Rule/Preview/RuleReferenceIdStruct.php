<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shopware\Commercial\RuleBuilderPreview\Domain\Rule\Preview;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Struct\Struct;

/**
 * @internal
 */
#[Package('business-ops')]
final class RuleReferenceIdStruct extends Struct
{
    public function __construct(private readonly string $referenceId)
    {
    }

    public function getId(): string
    {
        return $this->referenceId;
    }
}
