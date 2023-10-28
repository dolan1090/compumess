<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Storefront\Framework\Twig\Extension;

use Swag\CustomizedProducts\Storefront\Framework\Media\Validator\FileMediaUploadValidator;
use Swag\CustomizedProducts\Storefront\Framework\Media\Validator\ImageMediaUploadValidator;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\TemplateOptionEntity;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CustomizedProductsLoadValidMimeTypes extends AbstractExtension
{
    public function __construct(
        protected ImageMediaUploadValidator $imageValidator,
        protected FileMediaUploadValidator $fileValidator
    ) {
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('customized_product_get_valid_mime_types', $this->getValidMimeTypes(...), ['needs_context' => false]),
        ];
    }

    public function getValidMimeTypes(string $type, ?TemplateOptionEntity $option = null): array
    {
        if ($type === $this->imageValidator->getType()) {
            return $this->imageValidator->getMimeTypes($option);
        }

        if ($type === $this->fileValidator->getType()) {
            return $this->fileValidator->getMimeTypes($option);
        }

        return [];
    }
}
