<?php declare(strict_types=1);

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;
use Symfony\Bundle\DebugBundle\DebugBundle;
use Enqueue\Bundle\EnqueueBundle;
use Enqueue\MessengerAdapter\Bundle\EnqueueAdapterBundle;
use Shopware\Core\Framework\Framework;
use Shopware\Core\System\System;
use Shopware\Core\Content\Content;
use Shopware\Core\Checkout\Checkout;
use Shopware\Core\Profiling\Profiling;
use Shopware\Core\DevOps\DevOps;
use Shopware\Core\Maintenance\Maintenance;
use Shopware\Storefront\Storefront;

return [
    FrameworkBundle::class => ['all' => true],
    MonologBundle::class => ['all' => true],
    SensioFrameworkExtraBundle::class => ['all' => true],
    TwigBundle::class => ['all' => true],
    WebProfilerBundle::class => ['dev' => true, 'test' => true],
    DebugBundle::class => ['dev' => true, 'test' => true],
    EnqueueBundle::class => ['all' => true],
    EnqueueAdapterBundle::class => ['all' => true],
    Framework::class => ['all' => true],
    System::class => ['all' => true],
    Content::class => ['all' => true],
    Checkout::class => ['all' => true],
    Profiling::class => ['all' => true],
    DevOps::class => ['all' => true],
    Maintenance::class => ['all' => true],
    Storefront::class => ['all' => true],
];
