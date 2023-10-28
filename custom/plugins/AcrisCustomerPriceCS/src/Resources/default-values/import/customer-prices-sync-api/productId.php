// service init
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

$id = null;
$productNumber = null;
if(array_key_exists('productId', $data) === true && !empty($data['productId'])) {
    $id = $data['productId'];
}

if(array_key_exists('productNumber', $data) === true && !empty($data['productNumber'])) {
    $productNumber = $data['productNumber'];
}

if (empty($id) && empty($productNumber)) {
    if (!empty($data) && is_array($data) && array_key_exists('product', $data) && is_array($data['product']) && !empty($data['product'])) {
        if (array_key_exists('id', $data['product']) && !empty($data['product']['id'])) {
            $id = $data['product']['id'];
        }
        elseif (array_key_exists('productNumber', $data['product']) && !empty($data['product']['productNumber'])) {
            $productNumber = $data['product']['productNumber'];
        }
    }
}

if (empty($id) && empty($productNumber)) {
    throw new \Exception("Product number is required!");
}

if (empty($id) && !empty($productNumber)) {
    $productRepository = $this->container->get('product.repository');
    $id = $productRepository->searchIds((new Criteria())->addFilter(new EqualsFilter('productNumber', $productNumber)), $context)->firstId();

}

if(empty($id) === true) {
    throw new \Exception("Product with provided product number was not found!");

}

$value = $id;