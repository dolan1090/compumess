<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class FileUpload extends OptionType
{
    public const NAME = 'fileupload';

    private int $maxCount;

    private int $maxFileSize;

    private array $excludedExtensions;

    private array $files;

    public function getName(): string
    {
        return self::NAME;
    }

    public function getMaxCount(): int
    {
        return $this->maxCount;
    }

    public function setMaxCount(int $maxCount): void
    {
        $this->maxCount = $maxCount;
    }

    public function getMaxFileSize(): int
    {
        return $this->maxFileSize;
    }

    public function setMaxFileSize(int $maxFileSize): void
    {
        $this->maxFileSize = $maxFileSize;
    }

    public function getExcludedExtensions(): array
    {
        return $this->excludedExtensions;
    }

    public function setExcludedExtensions(array $excludedExtensions): void
    {
        $this->excludedExtensions = $excludedExtensions;
    }

    public function getFiles(): array
    {
        return $this->files;
    }

    public function setFiles(array $files): void
    {
        $this->files = $files;
    }

    public function getConstraints(): array
    {
        $constraints = parent::getConstraints();
        $constraints['maxCount'] = [new NotBlank(), new Type('int')];
        $constraints['maxFileSize'] = [new NotBlank(), new Type('int')];
        $constraints['excludedExtensions'] = [new Type('array')];

        return $constraints;
    }
}
