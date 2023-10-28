<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisher\VersionControlSystem\Internal;

class AffectedEntity
{
    private string $id;

    private string $name;

    private function __construct(
        string $id,
        string $name
    ) {
        $this->id = $id;
        $this->name = $name;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public static function create(string $id, string $name): self
    {
        return new AffectedEntity($id, $name);
    }
}
