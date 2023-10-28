// service init
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

$id = null;
$customerNumber = null;
if(array_key_exists('customerId', $data) === true && !empty($data['customerId'])) {
    $id = $data['customerId'];
}

if(array_key_exists('customerNumber', $data) === true && !empty($data['customerNumber'])) {
    $customerNumber = $data['customerNumber'];
}

if (empty($id) && empty($customerNumber)) {
    if (!empty($data) && is_array($data) && array_key_exists('customer', $data) && is_array($data['customer']) && !empty($data['customer'])) {
        if (array_key_exists('id', $data['customer']) && !empty($data['customer']['id'])) {
            $id = $data['customer']['id'];
        }
        elseif (array_key_exists('customerNumber', $data['customer']) && !empty($data['customer']['customerNumber'])) {
            $customerNumber = $data['customer']['customerNumber'];
        }
    }
}

if (empty($id) && empty($customerNumber)) {
    throw new \Exception("Customer number is required!");
}

if (empty($id) && !empty($customerNumber)) {
    $customerRepository = $this->container->get('customer.repository');
    $id = $customerRepository->searchIds((new Criteria())->addFilter(new EqualsFilter('customerNumber', $customerNumber)), $context)->firstId();

}

if(empty($id) === true) {
    throw new \Exception("Customer with provided customer number was not found!");

}

$value = $id;