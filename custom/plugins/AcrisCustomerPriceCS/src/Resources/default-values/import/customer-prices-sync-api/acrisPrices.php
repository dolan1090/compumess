// service init
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Content\Product\ProductEntity;

if(is_array($value) && !empty($value)) {
    if (!function_exists('convertCommaValue')) {
        function convertCommaValue($price): float
        {
            if(is_string($price)) {
                return floatval(str_replace(",", ".", str_replace(' ', '', $price)));
            } else {
                return floatval($price);
            }
        }
    }

    $customerPrices = $value;
    $value = [];

    $criteria = null;

    if (empty($tax) && !empty($data) && is_array($data)) {
        if (!empty($data['productId']) || !empty($data['productNumber'])) {
            $criteria = !empty($data['productId']) ? (new Criteria([$data['productId']])) : (new Criteria())->addFilter(new EqualsFilter('productNumber', $data['productNumber']));
        }

        if (array_key_exists('product', $data) && is_array($data['product']) && !empty($data['product'])) {
            if (array_key_exists('id', $data['product']) && !empty($data['product']['id'])) {
                $criteria = (new Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria([$data['product']['id']]));
            }
            elseif (array_key_exists('productNumber', $data['product']) && !empty($data['product']['productNumber'])) {
                $criteria = (new Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria())->addFilter(new EqualsFilter('productNumber', $data['product']['productNumber']));
            }
        }
    }

    if (empty($tax) && !empty($criteria) && $criteria instanceof Criteria) {
        $productRepository = $this->container->get('product.repository');

        $searchResult = $productRepository->search($criteria, $context);
        /** @var ProductEntity $product */
        $product = $searchResult->first();
        if ($product) {
            $tax = $product->getTax()->getTaxRate();
        }
    }
    if (empty($tax) && $tax !== 0) throw new \Exception("Is the product already imported? There is no existing tax found for it.");

    $defaultCurrencyId = $context->getCurrencyId();

    $customerPricesSorted = [];
    $currencyRepository = $this->container->get('currency.repository');

    // build customer prices array
    foreach ($customerPrices as $key => $customerPrice) {
        if(!is_array($customerPrice)) {
            continue;
        }

        if (!array_key_exists('price', $customerPrice) || empty($customerPrice['price'])) continue;

        $prices = $customerPrice['price'];

        if (array_key_exists('quantityStart', $customerPrice)) {
            $quantityStart = intval(convertCommaValue($customerPrice['quantityStart']));
            if ($quantityStart <= 1) $quantityStart = 1;
        }
        if (empty($quantityStart)) $quantityStart = 1;

        if (array_key_exists('quantityEnd', $customerPrice) && !empty($customerPrice['quantityEnd'])) {
            $quantityEnd = intval(convertCommaValue($customerPrice['quantityEnd']));
        } else {
            $quantityEnd = null;
        }

        foreach ($prices as $price) {
            if ((!array_key_exists('net', $price) || empty(convertCommaValue($price['net'])))
                && (!array_key_exists('gross', $price) || empty(convertCommaValue($price['gross'])))) {
                continue;
            }

            if (array_key_exists('currency', $price) && !empty($price['currency']) && array_key_exists('isoCode', $price['currency']) && !empty($price['currency']['isoCode'])) {
                // get currency by iso code
                $isoCode = $price['currency']['isoCode'];
                if(isset($this->conversionProperties['currency']) && isset($this->conversionProperties['currency'][$isoCode])) {
                    $currencyId = $this->conversionProperties['currency'][$isoCode];
                } else {
                    $searchResult = $currencyRepository->searchIds((new Criteria())->addFilter(new EqualsFilter("isoCode",
                        $isoCode)), $context);
                    $currencyId = $searchResult->firstId();

                    if (empty($currencyId)) {
                        throw new \Exception("The currency is missing!");
                    }
                }
                $this->conversionProperties['currency'][$isoCode] = $currencyId;
            } else {
                $currencyId = $defaultCurrencyId;
            }

            if (array_key_exists('currencyId', $price) && !empty($price['currencyId'])) {
                $currencyId = $price['currencyId'];
            } else {
                $price['currencyId'] = $currencyId;
            }

            $customerPricesSorted[$quantityStart]['currencyPrices'][$currencyId] = [
                $price
            ];
        }

        $customerPricesSorted[$quantityStart]['quantityStart'] = $quantityStart;
        $customerPricesSorted[$quantityStart]['quantityEnd'] = $quantityEnd;
    }

    // sort and set quantity to
    ksort($customerPricesSorted);

    $firstKey = array_key_first($customerPricesSorted);
    $lastKey = array_key_last($customerPricesSorted);

    krsort($customerPricesSorted);

    $fromBefore = null;
    foreach ($customerPricesSorted as $from => $quantityPrices) {
        if(!is_array($quantityPrices)) {
            unset($customerPricesSorted[$from]);
            continue;
        }

        if($from === $firstKey) {
            if($from > 1) {
                throw new \Exception("A scale price starting with 1 is missing!");
            }
            $customerPricesSorted[$from]['quantityStart'] = 1;
        } elseif($from === $lastKey) {
            $customerPricesSorted[$from]['quantityEnd'] = null;
        }

        if($fromBefore !== null) {
            $newTo = $fromBefore - 1;
            if(array_key_exists('quantityEnd', $customerPricesSorted[$from]) && !empty($customerPricesSorted[$from]['quantityEnd'])) {
                if($newTo !== $customerPricesSorted[$from]['quantityEnd']) {
                    throw new \Exception("The following to value is wrong. Given: " . $customerPricesSorted[$from]['quantityEnd'] . " Excpected: " . $newTo);
                }
            }
            $customerPricesSorted[$from]['quantityEnd'] = $newTo;
        }

        $fromBefore = $from;
    }

    ksort($customerPricesSorted);

    // now we calculate gross and net and fill up data
    foreach ($customerPricesSorted as $from => $quantityPrices) {
        $priceRule = [
            "quantityStart" => $quantityPrices['quantityStart']
        ];
        if($quantityPrices['quantityEnd']) {
            $priceRule['quantityEnd'] = $quantityPrices['quantityEnd'];
        }

        foreach ($quantityPrices['currencyPrices'] as $currencyId => $currencyPrices) {
            foreach ($currencyPrices as $i => $customerPrice) {
                if(!is_array($customerPrice)) {
                    unset($currencyPrices[$i]);
                    continue;
                }

                if (array_key_exists('net', $customerPrice) && array_key_exists('gross', $customerPrice)) {
                    $priceNet = convertCommaValue($customerPrice['net']);
                    $priceGross = convertCommaValue($customerPrice['gross']);
                }
                elseif (array_key_exists('net', $customerPrice) && !array_key_exists('gross', $customerPrice)) {
                    $priceNet = convertCommaValue($customerPrice['net']);
                    if ($tax === 0) {
                        $priceGross = $priceNet;
                    } else {
                        $priceGross = $priceNet + ($priceNet * $tax / 100);
                    }
                }
                elseif (!array_key_exists('net', $customerPrice) && array_key_exists('gross', $customerPrice)) {
                    $priceGross = convertCommaValue($customerPrice['gross']);
                    if($tax) {
                        $priceNet = $priceGross * 100 / (100 + $tax);
                    } else {
                        $priceNet = $priceGross;
                    }
                }
                elseif (!array_key_exists('net', $customerPrice) && !array_key_exists('gross', $customerPrice)) {
                    unset($currencyPrices[$i]);
                    continue;
                    //throw new \Exception("The net and gross prices are missing!");
                }

                $listPrice = null;

                if (array_key_exists('listPrice', $customerPrice) && !empty($customerPrice['listPrice'])) {
                    $advancedListPrice = $customerPrice['listPrice'];

                    //get currencyId for list price
                    if (array_key_exists('currency', $advancedListPrice) && !empty($advancedListPrice['currency']) && array_key_exists('isoCode', $advancedListPrice['currency']) && !empty($advancedListPrice['currency']['isoCode'])) {
                        // get currency by iso code
                        $isoCode = $advancedListPrice['currency']['isoCode'];
                        $searchResult = $currencyRepository->searchIds((new Criteria())->addFilter(new EqualsFilter("isoCode",
                            $isoCode)), $context);
                        $currencyId = $searchResult->firstId();

                        if (empty($currencyId)) {
                            throw new \Exception("The currency is missing!");
                        }
                    }

                    if (array_key_exists('currencyId', $advancedListPrice) && !empty($advancedListPrice['currencyId'])) {
                        $currencyId = $advancedListPrice['currencyId'];
                    }

                    if (array_key_exists('net', $advancedListPrice) && array_key_exists('gross', $advancedListPrice)) {
                        $listPriceNet = convertCommaValue($advancedListPrice['net']);
                        $listPriceGross = convertCommaValue($advancedListPrice['gross']);

                        $listPrice = [
                            "currencyId" => $currencyId,
                            "net" => $listPriceNet,
                            "gross" => $listPriceGross,
                            "linked" => true
                        ];
                    }
                    elseif (array_key_exists('net', $advancedListPrice) && !array_key_exists('gross', $advancedListPrice)) {
                        $listPriceNet = convertCommaValue($advancedListPrice['net']);
                        if ($tax === 0) {
                            $listPriceGross = $listPriceNet;
                        } else {
                            $listPriceGross = $listPriceNet + ($listPriceNet * $tax / 100);
                        }

                        $listPrice = [
                            "currencyId" => $currencyId,
                            "net" => $listPriceNet,
                            "gross" => $listPriceGross,
                            "linked" => true
                        ];
                    }
                    elseif (!array_key_exists('net', $advancedListPrice) && array_key_exists('gross', $advancedListPrice)) {
                        $listPriceGross = convertCommaValue($advancedListPrice['gross']);
                        if($tax) {
                            $listPriceNet = $listPriceGross * 100 / (100 + $tax);
                        } else {
                            $listPriceNet = $listPriceGross;
                        }

                        $listPrice = [
                            "currencyId" => $currencyId,
                            "net" => $listPriceNet,
                            "gross" => $listPriceGross,
                            "linked" => true
                        ];
                    }
                }

                /*if ($priceNet <= 0 && $priceGross <= 0) {
                    continue;
                }

                if ($priceNet <= 0) {
                    throw new \Exception("The product sales net price should be higher than 0!");
                }
                if ($priceGross <= 0) {
                    throw new \Exception("The product sales gross price should be higher than 0!");
                }
                if (!empty($listPrice) && is_array($listPrice)) {
                    if (array_key_exists('net', $listPrice) && $listPrice['net'] <= 0
                        && array_key_exists('gross', $listPrice) && $listPrice['gross'] <= 0) {
                        $listPrice = null;
                    } elseif (array_key_exists('net', $listPrice) && $listPrice['net'] <= 0) {
                        throw new \Exception("The product list net price should be higher than 0!");
                    } elseif (array_key_exists('gross', $listPrice) && $listPrice['gross'] <= 0) {
                        throw new \Exception("The product list gross price should be higher than 0!");
                    }
                }*/

                $priceRule['price'][] = [
                    "currencyId" => $currencyId,
                    "net" => $priceNet,
                    "gross" => $priceGross,
                    "linked" => true,
                    "listPrice" => $listPrice
                ];
            }
        }

        $value[] = $priceRule;
    }

    // every price must have a price with default currency
    foreach ($value as $key => $productPrice) {
        if(!is_array($productPrice) || !array_key_exists('price', $productPrice) || empty($productPrice['price'])) {
            unset($value[$key]);
        }

        $hasDefaultCurrencyPrice = false;
        foreach ($productPrice['price'] as $price) {
            if(!array_key_exists('currencyId', $price) || empty($price['currencyId'])) {
                throw new \Exception("No currency id was found for importing price!");
            }
            if($price['currencyId'] === $defaultCurrencyId) {
                $hasDefaultCurrencyPrice = true;
            }
        }
        if($hasDefaultCurrencyPrice !== true) {
            throw new \Exception("A default currency price is missing for import!");
        }
    }
} else {
    $value = [];
    $name = 'noCustomerPrice';
}
