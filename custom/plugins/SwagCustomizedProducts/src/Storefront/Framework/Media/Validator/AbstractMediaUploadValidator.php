<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Storefront\Framework\Media\Validator;

use Shopware\Storefront\Framework\Media\Exception\FileTypeNotAllowedException;
use Shopware\Storefront\Framework\Media\StorefrontMediaValidatorInterface;
use Shopware\Storefront\Framework\Media\Validator\MimeTypeValidationTrait;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\TemplateOptionEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;

abstract class AbstractMediaUploadValidator implements StorefrontMediaValidatorInterface
{
    use MimeTypeValidationTrait;

    /**
     * @return string[]
     */
    public function getMimeTypes(?TemplateOptionEntity $option = null): array
    {
        return \array_reduce($this->getFilteredExtensionsWithMimeTypes($option), static fn (array $accumulator, array $mimeTypes) => \array_merge($accumulator, $mimeTypes), []);
    }

    /**
     * @return array<string, string[]>
     */
    abstract public function getExtensionsWithMimeTypes(): array;

    public function getFilteredExtensionsWithMimeTypes(?TemplateOptionEntity $option = null): array
    {
        if ($option === null) {
            return $this->getExtensionsWithMimeTypes();
        }

        $excludedExtensions = $option->getTypeProperties()['excludedExtensions'] ?? [];

        if (empty($excludedExtensions) || !\is_array($excludedExtensions)) {
            return $this->getExtensionsWithMimeTypes();
        }

        return \array_filter($this->getExtensionsWithMimeTypes(), static function (string $mimeExtensions) use ($excludedExtensions) {
            foreach ($excludedExtensions as $excludedExtension) {
                if (\str_contains($mimeExtensions, \mb_strtolower($excludedExtension))) {
                    return false;
                }
            }

            return true;
        }, \ARRAY_FILTER_USE_KEY);
    }

    public function checkExcludedFileTypes(UploadedFile $file, TemplateOptionEntity $option): void
    {
        $excludedExtensions = $option->getTypeProperties()['excludedExtensions'] ?? [];

        if (empty($excludedExtensions)) {
            return;
        }

        $allowedTypes = $this->getFilteredExtensionsWithMimeTypes($option);

        $valid = $this->checkMimeType($file, $allowedTypes);

        if (!$valid) {
            $mimeType = $file->getMimeType() ?? '';

            throw new FileTypeNotAllowedException($mimeType, $this->getType());
        }
    }
}
