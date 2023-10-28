<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisher\VersionControlSystem\Data;

use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\User\UserDefinition;

class UserExtension extends EntityExtension
{
    public function getDefinitionClass(): string
    {
        return UserDefinition::class;
    }

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add((new OneToManyAssociationField('cmsPageDrafts', CmsPageDraftDefinition::class, 'owner_user_id', 'id'))->addFlags());
        $collection->add((new OneToManyAssociationField('cmsPageActivities', CmsPageActivityDefinition::class, 'user_id', 'id'))->addFlags());
    }
}
