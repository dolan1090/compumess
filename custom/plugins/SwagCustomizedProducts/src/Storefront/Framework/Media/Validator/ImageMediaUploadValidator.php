<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Storefront\Framework\Media\Validator;

use Shopware\Storefront\Framework\Media\Exception\FileTypeNotAllowedException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageMediaUploadValidator extends AbstractMediaUploadValidator
{
    final public const TYPE = 'customized_products_images';

    public function getExtensionsWithMimeTypes(): array
    {
        return [
            'jpe|jpg|jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'gif' => ['image/gif'],
            'webp' => ['image/webp'],
            'svg|svgz' => ['image/svg+xml'],
            'bmp' => ['image/bmp', 'image/x-ms-bmp'],
            'tif|tiff' => ['image/tiff'],
            'eps' => ['image/x-eps', 'application/postscript'],
        ];
    }

    public function getType(): string
    {
        return self::TYPE;
    }

    public function validate(UploadedFile $file): void
    {
        $fileMimeType = $file->getMimeType() ?? '';
        $valid = $this->checkMimeType($file, $this->getExtensionsWithMimeTypes());

        if (!$valid) {
            throw new FileTypeNotAllowedException($fileMimeType, $this->getType());
        }

        // Additional validation is skipped due to the fact `\getimagesize` doesn't work for the provided mime types
        $mimeTypesBlockedForAdditionalCheck = [
            'image/svg+xml',
            'application/postscript',
        ];
        if (\in_array($fileMimeType, $mimeTypesBlockedForAdditionalCheck, true)) {
            return;
        }

        // additional mime type validation
        // we detect the mime type over the `getimagesize` extension
        $imageSize = \getimagesize($file->getPath() . '/' . $file->getFileName());
        $mimeType = \is_array($imageSize) ? $imageSize['mime'] : '';
        if (!\in_array($mimeType, $this->getMimeTypes(), true)) {
            throw new FileTypeNotAllowedException($fileMimeType, $this->getType());
        }
    }
}
