<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisher\Common;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MariaDb1027Platform;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Command\UpdateCommand;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Validation\PreWriteValidationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UpdateChangeDetector implements EventSubscriberInterface
{
    private Connection $connection;

    /**
     * @var string[]
     */
    private array $activeEntities;

    /**
     * @param string[] $activeEntities
     */
    public function __construct(Connection $connection, array $activeEntities = [])
    {
        $this->connection = $connection;
        $this->activeEntities = $activeEntities;
    }

    public static function getSubscribedEvents(): array
    {
        return [PreWriteValidationEvent::class => 'detectChanges'];
    }

    public function detectChanges(PreWriteValidationEvent $event): void
    {
        $commands = $event->getCommands();
        $extension = UpdateChangeContextExtension::extract($event->getContext());

        foreach ($commands as $command) {
            if (!$command instanceof UpdateCommand) {
                continue;
            }

            if (!\in_array($command->getDefinition()->getClass(), $this->activeEntities, true)) {
                continue;
            }

            $extension->addResult(
                $command,
                $this->containsChanges($command)
            );
        }
    }

    private function containsChanges(UpdateCommand $command): bool
    {
        $payload = $command->getPayload();

        unset($payload['updated_at']);

        $conditions = [];
        $values = [];
        $data = \array_merge($command->getPrimaryKey(), $payload);
        $fields = $command->getDefinition()->getFields();

        $isMariaDb = $this->connection->getDatabasePlatform() instanceof MariaDb1027Platform;

        foreach ($data as $key => $value) {
            $field = $fields->getByStorageName($key);

            if ($field === null) {
                continue;
            }

            if (!$isMariaDb && $field instanceof JsonField) {
                $conditions[] = "`$key` = CAST(:$key AS JSON)";
            } else {
                $conditions[] = "`$key` = :$key";
            }

            $values[$key] = $value;
        }

        $query = \sprintf(
            'SELECT 1 AS id FROM `%s` WHERE %s LIMIT 1',
            $command->getDefinition()->getEntityName(),
            \implode(' AND ', $conditions)
        );

        return $this->connection->fetchOne($query, $values) !== '1';
    }
}
