<?php declare(strict_types=1);

namespace Shopware\Commercial\Captcha;

use Doctrine\DBAL\Connection;
use Shopware\Commercial\System\UninstallHandler;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Symfony\Component\DependencyInjection\ContainerInterface;

#[Package('checkout')]
class CaptchaUninstallHandler implements UninstallHandler
{
    private const CONFIG_KEY = 'core.basicInformation.activeCaptchasV2';

    public function uninstall(ContainerInterface $container, UninstallContext $context): void
    {
        $connection = $container->get(Connection::class);

        $connection->executeStatement(
            'UPDATE system_config SET configuration_value = JSON_REMOVE(configuration_value, "$._value.friendlyCaptcha") WHERE configuration_key = :key',
            ['key' => self::CONFIG_KEY]
        );
    }
}
