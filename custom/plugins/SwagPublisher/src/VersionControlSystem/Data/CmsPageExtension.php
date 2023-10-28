<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisher\VersionControlSystem\Data;

use Shopware\Core\Content\Cms\CmsPageDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class CmsPageExtension extends EntityExtension
{
    public function getDefinitionClass(): string
    {
        return CmsPageDefinition::class;
    }

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add((new OneToManyAssociationField('drafts', CmsPageDraftDefinition::class, 'cms_page_id', 'id'))->addFlags());
        $collection->add((new OneToManyAssociationField('activities', CmsPageActivityDefinition::class, 'cms_page_id', 'id'))->addFlags());
    }
}
