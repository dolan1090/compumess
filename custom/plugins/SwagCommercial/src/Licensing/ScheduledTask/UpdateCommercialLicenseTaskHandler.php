<?php declare(strict_types=1);

namespace Shopware\Commercial\Licensing\ScheduledTask;

use Shopware\Commercial\Licensing\LicenseUpdater;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * @internal
 */
#[Package('merchant-services')]
#[AsMessageHandler(handles: UpdateCommercialLicenseTask::class)]
final class UpdateCommercialLicenseTaskHandler extends ScheduledTaskHandler
{
    /**
     * @internal
     */
    public function __construct(
        EntityRepository $scheduledTaskRepository,
        private readonly LicenseUpdater $licenseUpdater
    ) {
        parent::__construct($scheduledTaskRepository);
    }

    public function run(): void
    {
        $this->licenseUpdater->sync();
    }
}
