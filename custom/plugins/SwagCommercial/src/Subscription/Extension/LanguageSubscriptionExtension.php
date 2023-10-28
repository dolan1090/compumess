<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Extension;

use Shopware\Commercial\Subscription\Entity\Subscription\SubscriptionDefinition;
use Shopware\Commercial\Subscription\Entity\SubscriptionInterval\Aggregate\SubscriptionIntervalTranslation\SubscriptionIntervalTranslationDefinition;
use Shopware\Commercial\Subscription\Entity\SubscriptionPlan\Aggregate\SubscriptionPlanTranslation\SubscriptionPlanTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\RestrictDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\Language\LanguageDefinition;

#[Package('checkout')]
class LanguageSubscriptionExtension extends EntityExtension
{
    public function getDefinitionClass(): string
    {
        return LanguageDefinition::class;
    }

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new OneToManyAssociationField(
                'subscriptionIntervalTranslations',
                SubscriptionIntervalTranslationDefinition::class,
                'language_id',
            ))->addFlags(new CascadeDelete()),
        );

        $collection->add(
            (new OneToManyAssociationField(
                'subscriptionPlanTranslations',
                SubscriptionPlanTranslationDefinition::class,
                'language_id',
            ))->addFlags(new CascadeDelete()),
        );

        $collection->add(
            (new OneToManyAssociationField(
                'subscriptions',
                SubscriptionDefinition::class,
                'language_id',
            ))->addFlags(new RestrictDelete()),
        );
    }
}
