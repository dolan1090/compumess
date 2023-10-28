<?php declare(strict_types=1);

namespace Acris\CustomerPrice\Core\Framework\DataAbstractionLayer\Write;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Write\EntityExistence;
use Shopware\Core\Framework\DataAbstractionLayer\Write\WriteParameterBag;
use Shopware\Core\Framework\DataAbstractionLayer\Write\WriteCommandExtractor as ParentClass;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\DependencyInjection\ContainerInterface;

class WriteCommandExtractor extends ParentClass
{
    public function __construct(
        private readonly ParentClass $writeCommandExtractor,
        private readonly ContainerInterface $container,
        private readonly Connection $connection
    )
    {
    }

    public function extract(array $rawData, WriteParameterBag $parameters): array
    {
        $context = $parameters->getContext()->getContext();
        $definition = $parameters->getDefinition();

        if($definition->getEntityName() === 'acris_customer_price' && !empty($rawData) && array_key_exists('productId', $rawData) && !empty($rawData['productId']) && array_key_exists('customerId', $rawData) && !empty($rawData['customerId'])) {
            $customerPriceRepository = $this->container->get('acris_customer_price.repository');

            $id = $customerPriceRepository->searchIds((new Criteria())->addFilter(new MultiFilter(MultiFilter::CONNECTION_AND, [
                new EqualsFilter('productId', $rawData['productId']),
                new EqualsFilter('customerId', $rawData['customerId'])
            ])), $context)->firstId();

            if (!empty($id) && Uuid::isValid($id)) {
                $rawData['id'] = $id;

                if(array_key_exists('acrisPrices', $rawData) && is_array($rawData['acrisPrices'])) {
                    // remove existing advanced prices
                    $this->connection->executeStatement('DELETE FROM `acris_customer_advanced_price` WHERE `customer_price_id` = :id', ['id' => Uuid::fromHexToBytes($id)]);

                    // set id for advanced prices
                    foreach ($rawData['acrisPrices'] as $key => $acrisPrice) {
                        if(is_array($acrisPrice)) {
                            $rawData['acrisPrices'][$key]['customerPriceId'] = $id;
                        }
                    }
                }
            }
        }

        return $this->writeCommandExtractor->extract($rawData,$parameters);
    }

    public function normalize(EntityDefinition $definition, array $rawData, WriteParameterBag $parameters): array
    {
        return $this->writeCommandExtractor->normalize($definition, $rawData, $parameters);
    }

    public function normalizeSingle(EntityDefinition $definition, array $data, WriteParameterBag $parameters): array
    {
        return $this->writeCommandExtractor->normalizeSingle($definition, $data, $parameters);
    }

    public function extractJsonUpdate($data, EntityExistence $existence, WriteParameterBag $parameters): void
    {
        $this->writeCommandExtractor->extractJsonUpdate($data, $existence, $parameters);
    }
}
