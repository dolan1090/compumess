<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisher\VersionControlSystem\Data;

use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class MediaExtension extends EntityExtension
{
    public function getDefinitionClass(): string
    {
        return MediaDefinition::class;
    }

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add((new OneToManyAssociationField('cmsPageDrafts', CmsPageDraftDefinition::class, 'preview_media_id', 'id'))->addFlags());
    }
}
