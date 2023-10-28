<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisher\VersionControlSystem\Data;

use Shopware\Core\Content\Cms\CmsPageDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\WriteProtected;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\User\UserDefinition;

class CmsPageActivityDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'cms_page_activity';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return ActivityCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),

            (new FkField('cms_page_id', 'pageId', CmsPageDefinition::class))->setFlags(new Required(), new WriteProtected(Context::SYSTEM_SCOPE)),
            (new ReferenceVersionField(CmsPageDefinition::class))->setFlags(new Required(), new WriteProtected(Context::SYSTEM_SCOPE)),

            (new FkField('user_id', 'userId', UserDefinition::class))->setFlags(new WriteProtected(Context::SYSTEM_SCOPE)),

            (new StringField('draft_version_id', 'draftVersion'))->setFlags(new WriteProtected(Context::SYSTEM_SCOPE)),
            (new StringField('name', 'name'))->setFlags(new WriteProtected(Context::SYSTEM_SCOPE)),
            (new JsonField('details', 'details'))->setFlags(new WriteProtected(Context::SYSTEM_SCOPE)),

            (new BoolField('is_merged', 'isMerged'))->setFlags(new WriteProtected(Context::SYSTEM_SCOPE)),
            (new BoolField('is_discarded', 'isDiscarded'))->setFlags(new WriteProtected(Context::SYSTEM_SCOPE)),
            (new BoolField('is_released_as_new', 'isReleasedAsNew'))->setFlags(new WriteProtected(Context::SYSTEM_SCOPE)),

            new ManyToOneAssociationField('cmsPage', 'cms_page_id', CmsPageDefinition::class, 'id', false),
            new ManyToOneAssociationField('user', 'user_id', UserDefinition::class, 'id', false),
        ]);
    }
}
