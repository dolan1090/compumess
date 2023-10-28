<?php
declare(strict_types=1);

namespace Shopware\Commercial\ReviewSummary\Extension\Product;

use Shopware\Commercial\ReviewSummary\Entity\ProductReviewSummary\ProductReviewSummaryDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Log\Package;

#[Package('inventory')]
class ProductReviewSummaryExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new OneToManyAssociationField('reviewSummaries', ProductReviewSummaryDefinition::class, 'product_id'))
                ->addFlags(new ApiAware(), new CascadeDelete())
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getDefinitionClass(): string
    {
        return ProductDefinition::class;
    }
}
