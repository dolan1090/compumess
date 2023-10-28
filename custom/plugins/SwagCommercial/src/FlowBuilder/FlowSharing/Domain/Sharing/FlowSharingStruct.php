<?php declare(strict_types=1);

namespace Shopware\Commercial\FlowBuilder\FlowSharing\Domain\Sharing;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Struct\Struct;

#[Package('business-ops')]
class FlowSharingStruct extends Struct
{
    /**
     * @internal
     *
     * @param array<string, mixed> $flow
     * @param array<array<array<string, mixed>>> $dataIncluded
     * @param array<string, array<string, array<string, mixed>>> $referenceIncluded
     * @param array<string, string|array<int, array<string, string>>> $requirements
     */
    public function __construct(
        protected array $flow,
        protected array $dataIncluded = [],
        protected array $referenceIncluded = [],
        protected array $requirements = []
    ) {
        $this->flow = $flow;
        $this->dataIncluded = $dataIncluded;
        $this->referenceIncluded = $referenceIncluded;
        $this->requirements = $requirements;
    }

    /**
     * @return array<string, mixed>
     */
    public function getFlow(): array
    {
        return $this->flow;
    }

    /**
     * @param array<string, mixed> $flow
     */
    public function setFlow(array $flow): void
    {
        $this->flow = $flow;
    }

    /**
     * @param array<string, array<string, mixed>> $data
     */
    public function addReference(string $name, array $data): void
    {
        $this->referenceIncluded[$name] = $data;
    }

    /**
     * @param array<string, string|array<int, array<string, string>>> $requirement
     */
    public function addRequirement(array $requirement): void
    {
        $this->requirements = [...$requirement, ...$this->requirements];
    }

    /**
     * @param array<string, array<string, mixed>> $data
     */
    public function addData(string $name, array $data): void
    {
        $this->dataIncluded[$name] = $data;
    }
}
