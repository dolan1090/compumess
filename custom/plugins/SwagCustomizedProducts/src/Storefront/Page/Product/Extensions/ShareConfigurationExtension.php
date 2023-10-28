<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Storefront\Page\Product\Extensions;

use Shopware\Core\Framework\Struct\Struct;

class ShareConfigurationExtension extends Struct
{
    /**
     * @var array
     */
    protected $configuration;

    public function getConfiguration(): array
    {
        return $this->configuration;
    }
}
