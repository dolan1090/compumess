<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateConfiguration\SalesChannel;

use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\System\SalesChannel\StoreApiResponse;
use Swag\CustomizedProducts\Template\Aggregate\TemplateConfiguration\TemplateConfigurationCollection;

class TemplateConfigurationListResponse extends StoreApiResponse
{
    /**
     * @var EntitySearchResult
     */
    protected $object;

    public function __construct(EntitySearchResult $object)
    {
        parent::__construct($object);
    }

    public function getTemplateConfigurations(): TemplateConfigurationCollection
    {
        /** @var TemplateConfigurationCollection $collection */
        $collection = $this->object->getEntities();

        return $collection;
    }
}
