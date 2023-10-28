<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateConfiguration;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Swag\CustomizedProducts\Template\Aggregate\TemplateConfiguration\Aggregate\TemplateConfigurationShareCollection;
use Swag\CustomizedProducts\Template\TemplateEntity;

class TemplateConfigurationEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var string
     */
    protected $versionId;

    /**
     * @var string
     */
    protected $hash;

    /**
     * @var array
     */
    protected $configuration;

    /**
     * @var string
     */
    protected $templateId;

    /**
     * @var TemplateEntity|null
     */
    protected $template;

    /**
     * @var TemplateConfigurationShareCollection|null
     */
    protected $templateConfigurationShares;

    public function getVersionId(): string
    {
        return $this->versionId;
    }

    public function setVersionId(string $versionId): void
    {
        $this->versionId = $versionId;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function setHash(string $hash): void
    {
        $this->hash = $hash;
    }

    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    public function setConfiguration(array $configuration): void
    {
        $this->configuration = $configuration;
    }

    public function getTemplateId(): string
    {
        return $this->templateId;
    }

    public function setTemplateId(string $templateId): void
    {
        $this->templateId = $templateId;
    }

    public function getTemplate(): ?TemplateEntity
    {
        return $this->template;
    }

    public function setTemplate(TemplateEntity $template): void
    {
        $this->template = $template;
    }

    public function getTemplateConfigurationShares(): ?TemplateConfigurationShareCollection
    {
        return $this->templateConfigurationShares;
    }

    public function setTemplateConfigurationShares(TemplateConfigurationShareCollection $templateConfigurationShares): void
    {
        $this->templateConfigurationShares = $templateConfigurationShares;
    }
}
