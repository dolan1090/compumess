<?php declare(strict_types=1);

namespace Shopware\Commercial\ExportAssistant\Message;

use Shopware\Commercial\Licensing\License;
use Shopware\Core\Content\ImportExport\Aggregate\ImportExportLog\ImportExportLogEntity;
use Shopware\Core\Content\ImportExport\ImportExportFactory;
use Shopware\Core\Content\ImportExport\Message\ImportExportHandler;
use Shopware\Core\Content\ImportExport\Message\ImportExportMessage;
use Shopware\Core\Content\ImportExport\Struct\Progress;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\RequestCriteriaBuilder;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @final
 */
#[Package('system-settings')]
class ImportExportHandlerDecorator
{
    /**
     * @internal
     */
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly ImportExportFactory $importExportFactory,
        private readonly RequestCriteriaBuilder $requestCriteriaBuilder,
        private readonly DefinitionInstanceRegistry $registry,
        private readonly ImportExportHandler $decorated
    ) {
    }

    public function __invoke(ImportExportMessage $message): void
    {
        if (!License::get('EXPORT_ASSISTANT-0490710')) {
            $this->decorated->__invoke($message);

            return;
        }

        if ($message->getActivity() !== ImportExportLogEntity::ACTIVITY_EXPORT) {
            $this->decorated->__invoke($message);

            return;
        }

        $importExport = $this->importExportFactory->create($message->getLogId(), 50, 50);
        $logEntity = $importExport->getLogEntity();

        if ($logEntity->getState() === Progress::STATE_ABORTED) {
            return;
        }

        $criteria = new Criteria();

        if (isset($logEntity->getConfig()['parameters']['criteria'])) {
            $rawCriteria = $logEntity->getConfig()['parameters']['criteria'];
            $definition = $this->registry->getByEntityName($logEntity->getConfig()['parameters']['sourceEntity']);
            $criteria = $this->requestCriteriaBuilder->fromArray($rawCriteria, $criteria, $definition, $message->getContext());
        }

        $progress = $importExport->export($message->getContext(), $criteria, $message->getOffset());

        if (!$progress->isFinished()) {
            $this->messageBus->dispatch(new ImportExportMessage(
                $message->getContext(),
                $logEntity->getId(),
                $logEntity->getActivity(),
                $progress->getOffset()
            ));
        }
    }
}
