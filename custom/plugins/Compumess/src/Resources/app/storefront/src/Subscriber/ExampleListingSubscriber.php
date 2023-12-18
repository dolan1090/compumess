<?php

class ExampleListingSubscriber implements EventSubscriberInterface
{
    // register event
    public static function getSubscribedEvents(): array
    {
        return [
            ProductListingCollectFilterEvent::class => 'addFilter'
        ];
    }

    public function addFilter(ProductListingCollectFilterEvent $event): void
    {
        // fetch existing filters
        $filters = $event->getFilters();
        $request = $event->getRequest();

        $filtered = (bool) $request->get('migration_Compumess57Live_product_series');

        $filter = new Filter(
            // unique name of the filter
            'migration_Compumess57Live_product_series',

            // defines if this filter is active
            $filtered,

            // Defines aggregations behind a filter. A filter can contain multiple aggregations like properties
            [
                new FilterAggregation(
                    'active-filter',
                    new MaxAggregation('active', 'product.customFields.migration_Compumess57Live_product_series'),
                    [new EqualsFilter('product.customFields.migration_Compumess57Live_product_series', true)]
                ),
            ],


            // defines the DAL filter which should be added to the criteria   
            new EqualsFilter('product.customFields.migration_Compumess57Live_product_series', true),

            // defines the values which will be added as currentFilter to the result
            $filtered
        );

        // Add your custom filter
        $filters->add($filter);
    }
}