<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Storefront\Framework\Media\Validator;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use Shopware\Storefront\Framework\Media\Exception\FileTypeNotAllowedException;
use Swag\CustomizedProducts\Storefront\Framework\Media\Validator\AbstractMediaUploadValidator;
use Swag\CustomizedProducts\Storefront\Framework\Media\Validator\ImageMediaUploadValidator;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\TemplateOptionEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageUploadMediaValidatorTest extends TestCase
{
    use KernelTestBehaviour;

    final public const FIXTURE_DIR = __DIR__ . '/fixtures';

    private AbstractMediaUploadValidator $validator;

    protected function setUp(): void
    {
        $this->validator = $this->getContainer()->get(ImageMediaUploadValidator::class);
    }

    public function testUploadImage(): void
    {
        $file = $this->getUploadFixture('image.bmp');
        $this->validator->checkExcludedFileTypes($file, new TemplateOptionEntity());
        $this->validator->validate($file);
        static::assertTrue(true);

        $file = $this->getUploadFixture('image.eps');
        $this->validator->checkExcludedFileTypes($file, new TemplateOptionEntity());
        $this->validator->validate($file);
        static::assertTrue(true);

        $file = $this->getUploadFixture('image.png');
        $this->validator->checkExcludedFileTypes($file, new TemplateOptionEntity());
        $this->validator->validate($file);
        static::assertTrue(true);

        $file = $this->getUploadFixture('image.svg');
        $this->validator->checkExcludedFileTypes($file, new TemplateOptionEntity());
        $this->validator->validate($file);
        static::assertTrue(true);

        $file = $this->getUploadFixture('image.tif');
        $this->validator->checkExcludedFileTypes($file, new TemplateOptionEntity());
        $this->validator->validate($file);
        static::assertTrue(true);

        $file = $this->getUploadFixture('image.webp');
        $this->validator->checkExcludedFileTypes($file, new TemplateOptionEntity());
        $this->validator->validate($file);
        static::assertTrue(true);
    }

    public function testUploadDocument(): void
    {
        $file = $this->getUploadFixture('empty.pdf');
        self::expectException(FileTypeNotAllowedException::class);

        $this->validator->checkExcludedFileTypes($file, new TemplateOptionEntity());
        $this->validator->validate($file);
    }

    public function testUploadImageWithExcludedFileExtensionMatching(): void
    {
        $file = $this->getUploadFixture('image.png');
        $option = new TemplateOptionEntity();
        $option->setTypeProperties(['excludedExtensions' => ['png']]);
        self::expectException(FileTypeNotAllowedException::class);

        $this->validator->checkExcludedFileTypes($file, $option);
        $this->validator->validate($file);
    }

    public function testUploadImageWithExcludedFileExtensionNotMatching(): void
    {
        $file = $this->getUploadFixture('image.png');
        $option = new TemplateOptionEntity();
        $option->setTypeProperties(['excludedExtensions' => ['jpg']]);

        $this->validator->checkExcludedFileTypes($file, $option);
        $this->validator->validate($file);
        static::assertTrue(true);
    }

    public function testUploadImageWithInvalidMimeType(): void
    {
        $file = new UploadedFile(self::FIXTURE_DIR . '/empty.pdf', 'invalid.png', 'image/png', null, true);

        self::expectException(FileTypeNotAllowedException::class);

        $this->validator->checkExcludedFileTypes($file, new TemplateOptionEntity());
        $this->validator->validate($file);
    }

    private function getUploadFixture(string $filename): UploadedFile
    {
        return new UploadedFile(self::FIXTURE_DIR . '/' . $filename, $filename, null, null, true);
    }
}
