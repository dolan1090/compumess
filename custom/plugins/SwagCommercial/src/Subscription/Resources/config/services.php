<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Shopware\Core\Checkout\Cart\Address\AddressValidator;
use Shopware\Core\Checkout\Cart\Delivery\DeliveryProcessor;
use Shopware\Core\Checkout\Cart\Delivery\DeliveryValidator;
use Shopware\Core\Checkout\Cart\LineItem\LineItemValidator;
use Shopware\Core\Checkout\Payment\Cart\PaymentMethodValidator;
use Shopware\Core\Content\Product\Cart\ProductCartProcessor;
use Shopware\Core\Content\Product\Cart\ProductLineItemValidator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

return static function (ContainerBuilder $container): void {
    $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/services'));

    $loader->load('cart/calculation.xml');
    $loader->load('cart/route.xml');
    $loader->load('cart/validation.xml');
    $loader->load('order/generation.xml');
    $loader->load('storefront/controller.xml');
    $loader->load('storefront/page-loader.xml');
    $loader->load('storefront/subscriber.xml');
    $loader->load('administration.xml');
    $loader->load('content.xml');
    $loader->load('dal.xml');
    $loader->load('demodata.xml');
    $loader->load('event.xml');
    $loader->load('routing.xml');
    $loader->load('rules.xml');
    $loader->load('store-api.xml');
    $loader->load('util.xml');

    // processors & collectors
    $container->getDefinition(ProductCartProcessor::class)->addTag('subscription.cart.processor', ['priority' => 5000]);
    $container->getDefinition(ProductCartProcessor::class)->addTag('subscription.cart.collector', ['priority' => 5000]);
    $container->getDefinition(DeliveryProcessor::class)->addTag('subscription.cart.processor', ['priority' => -5000]);
    $container->getDefinition(DeliveryProcessor::class)->addTag('subscription.cart.collector', ['priority' => -5000]);

    // validators
    $container->getDefinition(PaymentMethodValidator::class)->addTag('subscription.cart.validator');
    $container->getDefinition(DeliveryValidator::class)->addTag('subscription.cart.validator');
    $container->getDefinition(LineItemValidator::class)->addTag('subscription.cart.validator');
    $container->getDefinition(AddressValidator::class)->addTag('subscription.cart.validator');
    $container->getDefinition(ProductLineItemValidator::class)->addTag('subscription.cart.validator');
};
