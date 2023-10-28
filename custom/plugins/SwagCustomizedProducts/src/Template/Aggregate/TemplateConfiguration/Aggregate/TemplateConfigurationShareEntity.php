<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateConfiguration\Aggregate;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Swag\CustomizedProducts\Template\Aggregate\TemplateConfiguration\TemplateConfigurationEntity;

class TemplateConfigurationShareEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var string
     */
    protected $versionId;

    /**
     * @var bool
     */
    protected $oneTime;

    /**
     * @var string
     */
    protected $templateConfigurationId;

    /**
     * @var TemplateConfigurationEntity
     */
    protected $templateConfiguration;

    public function getVersionId(): string
    {
        return $this->versionId;
    }

    public function setVersionId(string $versionId): void
    {
        $this->versionId = $versionId;
    }

    public function isOneTime(): bool
    {
        return $this->oneTime;
    }

    public function setOneTime(bool $oneTime): void
    {
        $this->oneTime = $oneTime;
    }

    public function getTemplateConfigurationId(): string
    {
        return $this->templateConfigurationId;
    }

    public function setTemplateConfigurationId(string $templateConfigurationId): void
    {
        $this->templateConfigurationId = $templateConfigurationId;
    }

    public function getTemplateConfiguration(): TemplateConfigurationEntity
    {
        return $this->templateConfiguration;
    }

    public function setTemplateConfiguration(TemplateConfigurationEntity $templateConfiguration): void
    {
        $this->templateConfiguration = $templateConfiguration;
    }
}
