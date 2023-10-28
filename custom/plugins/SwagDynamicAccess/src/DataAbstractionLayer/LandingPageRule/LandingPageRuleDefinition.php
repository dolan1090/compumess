<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\DynamicAccess\DataAbstractionLayer\LandingPageRule;

use Shopware\Core\Content\LandingPage\LandingPageDefinition;
use Shopware\Core\Content\Rule\RuleDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\MappingEntityDefinition;

class LandingPageRuleDefinition extends MappingEntityDefinition
{
    public const ENTITY_NAME = 'swag_dynamic_access_landing_page_rule';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return LandingPageRuleCollection::class;
    }

    public function getEntityClass(): string
    {
        return LandingPageRuleEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new FkField(
                LandingPageDefinition::ENTITY_NAME . '_id',
                'landingPageId',
                LandingPageDefinition::class
            ))->addFlags(new PrimaryKey(), new Required(), new ApiAware()),
            (new ReferenceVersionField(LandingPageDefinition::class))->addFlags(new PrimaryKey(), new Required(), new ApiAware()),
            (new ManyToOneAssociationField(
                'landingPage',
                LandingPageDefinition::ENTITY_NAME . '_id',
                LandingPageDefinition::class,
                'id',
                false
            ))->addFlags(new CascadeDelete(), new ApiAware()),

            (new FkField(
                RuleDefinition::ENTITY_NAME . '_id',
                'ruleId',
                RuleDefinition::class
            ))->addFlags(new PrimaryKey(), new Required(), new ApiAware()),
            (new ManyToOneAssociationField(
                'rule',
                RuleDefinition::ENTITY_NAME . '_id',
                RuleDefinition::class,
                'id',
                false
            ))->addFlags(new CascadeDelete(), new ApiAware()),
        ]);
    }
}
