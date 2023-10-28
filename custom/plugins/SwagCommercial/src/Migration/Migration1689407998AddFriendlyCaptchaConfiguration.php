<?php declare(strict_types=1);

namespace Shopware\Commercial\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('checkout')]
class Migration1689407998AddFriendlyCaptchaConfiguration extends MigrationStep
{
    private const CONFIG_KEY = 'core.basicInformation.activeCaptchasV2';

    /**
     * @var array<string, array<string, string|bool>>
     */
    private array $captchaItems = [
        'friendlyCaptcha' => [
            'name' => 'friendlyCaptcha',
            'isActive' => true,
        ],
    ];

    public function getCreationTimestamp(): int
    {
        return 1689407998;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement(
            'UPDATE system_config SET configuration_value = JSON_MERGE_PATCH(
                configuration_value,
                :captchaItems
            ) WHERE configuration_key = :key',
            [
                'captchaItems' => json_encode(['_value' => $this->captchaItems], \JSON_THROW_ON_ERROR),
                'key' => self::CONFIG_KEY,
            ]
        );
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
