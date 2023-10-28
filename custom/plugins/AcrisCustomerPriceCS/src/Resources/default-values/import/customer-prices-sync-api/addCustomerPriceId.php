if(array_key_exists('productId', $convertedData) === false || empty($convertedData['productId'])) {
    throw new \Exception("Product id is required!");
}
if(array_key_exists('customerId', $convertedData) === false || empty($convertedData['customerId'])) {
    throw new \Exception("Customer id is required!");
}

$productId  = $convertedData['productId'];
$customerId  = $convertedData['customerId'];
$customerPriceRepository = $this->container->get('acris_customer_price.repository');
$id = $customerPriceRepository->searchIds((new \Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria())->addFilter(new \Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter(\Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter::CONNECTION_AND, [
    new \Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter('productId', $productId),
    new \Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter('customerId', $customerId)
])), $context)->firstId();

if(empty($id) === true) {
    $id = \Shopware\Core\Framework\Uuid\Uuid::randomHex();
}

$value = $id;
$name = 'id';
