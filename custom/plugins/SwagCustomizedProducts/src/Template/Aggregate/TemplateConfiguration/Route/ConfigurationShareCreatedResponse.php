<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateConfiguration\Route;

use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\System\SalesChannel\StoreApiResponse;

class ConfigurationShareCreatedResponse extends StoreApiResponse
{
    /**
     * @var ArrayStruct<string, string>
     */
    protected $object;

    public function __construct(string $shareUrl)
    {
        parent::__construct(new ArrayStruct(['shareUrl' => $shareUrl]));
    }

    public function getShareUrl(): string
    {
        return $this->object->get('shareUrl');
    }
}
