<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Profile\Writer;

use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Write\EntityWriterInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Write\WriteContext;
use Swag\CustomizedProducts\Profile\Shopware\DataSelection\DataSet\ValueDataSet;
use SwagMigrationAssistant\Migration\Writer\AbstractWriter;

class ValueWriter extends AbstractWriter
{
    public function __construct(
        EntityWriterInterface $entityWriter,
        EntityDefinition $definition,
        private readonly DefinitionInstanceRegistry $registry
    ) {
        parent::__construct($entityWriter, $definition);
    }

    public function supports(): string
    {
        return ValueDataSet::getEntity();
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<int|string, mixed>
     */
    public function writeData(array $data, Context $context): array
    {
        $mediaData = [];

        foreach ($data as &$entry) {
            if (isset($entry['media'])) {
                $mediaData[] = $entry['media'];
                unset($entry['media']);
            }
        }

        $writeResults = [];
        if (\count($mediaData) > 0) {
            $context->scope(Context::SYSTEM_SCOPE, function (Context $context) use ($mediaData, &$writeResults): void {
                $writeResults = $this->entityWriter->upsert(
                    $this->registry->get(MediaDefinition::class),
                    $mediaData,
                    WriteContext::createFromContext($context)
                );
            });
        }

        $writeResults[] = parent::writeData($data, $context);

        return $writeResults;
    }
}
