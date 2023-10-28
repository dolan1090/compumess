<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Domain\Validation;

use Shopware\Commercial\B2B\EmployeeManagement\Domain\Permission\PermissionEvent;
use Shopware\Commercial\B2B\EmployeeManagement\Domain\Permission\PermissionEventCollection;
use Shopware\Commercial\B2B\EmployeeManagement\Domain\Permission\PermissionEventCollector;
use Shopware\Commercial\B2B\EmployeeManagement\Entity\Role\RoleDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Command\InsertCommand;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Command\UpdateCommand;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Validation\PreWriteValidationEvent;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Validation\WriteConstraintViolationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Contracts\Service\ResetInterface;

/**
 * @internal
 */
#[Package('checkout')]
class RolePermissionsValidateSubscriber implements EventSubscriberInterface, ResetInterface
{
    final public const VIOLATION_PERMISSIONS_INVALID = 'permissions_invalid';
    final public const VIOLATION_DEPENDENCIES_MISSING = 'permission_dependencies_missing';

    private ?PermissionEventCollection $collection = null;

    public function __construct(
        private readonly PermissionEventCollector $collector,
    ) {
    }

    /**
     * @return array<string, string|array{0: string, 1: int}|list<array{0: string, 1?: int}>>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            PreWriteValidationEvent::class => 'preValidate',
        ];
    }

    public function preValidate(PreWriteValidationEvent $event): void
    {
        $writeCommands = $event->getCommands();

        foreach ($writeCommands as $command) {
            $violations = new ConstraintViolationList();

            if (!$command instanceof InsertCommand && !$command instanceof UpdateCommand) {
                continue;
            }

            if ($command->getDefinition()->getClass() !== RoleDefinition::class) {
                continue;
            }

            $payload = $command->getPayload();

            if (empty($payload['permissions'])) {
                continue;
            }

            $permissions = $payload['permissions'];
            $permissions = \is_string($permissions) ? json_decode($permissions, true, 512, \JSON_THROW_ON_ERROR) : $permissions;

            \assert(\is_array($permissions));

            if (!$this->collection) {
                $this->collection = $this->collector->collect($event->getContext());
            }

            $validPermissions = $this->collection->map(fn (PermissionEvent $definition) => $definition->getPermissionName());
            /** @var string[][] $dependencyMap */
            $dependencyMap = $this->collection->map(function (PermissionEvent $definition) use ($permissions): array {
                if (!\in_array($definition->getPermissionName(), $permissions, true)) {
                    return [];
                }

                return $definition->getPermissionDependencies();
            });

            /** @var string[] $dependencies */
            $dependencies = array_merge(...$dependencyMap);

            $invalidPermission = array_diff($permissions, $validPermissions);
            if (\count($invalidPermission) > 0) {
                $violations->add(
                    $this->buildViolation(
                        'Use of invalid permissions: {{ permissions }}',
                        ['{{ permissions }}' => implode(', ', $invalidPermission)],
                        '/permissions',
                        json_encode($permissions, \JSON_THROW_ON_ERROR),
                        self::VIOLATION_PERMISSIONS_INVALID
                    )
                );
            }

            $missingDependencies = array_diff($dependencies, $permissions);
            if (\count($missingDependencies) > 0) {
                $violations->add(
                    $this->buildViolation(
                        'Missing required permission dependencies: {{ permissions }}',
                        ['{{ permissions }}' => implode(', ', $missingDependencies)],
                        '/permissions',
                        json_encode($permissions, \JSON_THROW_ON_ERROR),
                        self::VIOLATION_DEPENDENCIES_MISSING
                    )
                );
            }

            if ($violations->count() > 0) {
                $event->getExceptions()->add(new WriteConstraintViolationException($violations, $command->getPath()));
            }
        }
    }

    public function reset(): void
    {
        $this->collection = null;
    }

    /**
     * @param array<string, string> $parameters
     */
    private function buildViolation(
        string $messageTemplate,
        array $parameters,
        string $propertyPath,
        string $invalidValue,
        string $code
    ): ConstraintViolationInterface {
        return new ConstraintViolation(
            str_replace(array_keys($parameters), array_values($parameters), $messageTemplate),
            $messageTemplate,
            $parameters,
            null,
            $propertyPath,
            $invalidValue,
            null,
            $code
        );
    }
}
