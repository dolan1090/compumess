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
use Swag\CustomizedProducts\Storefront\Framework\Media\Validator\FileMediaUploadValidator;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploadMediaValidatorTest extends TestCase
{
    use KernelTestBehaviour;

    final public const FIXTURE_DIR = __DIR__ . '/fixtures';

    private AbstractMediaUploadValidator $validator;

    protected function setUp(): void
    {
        $this->validator = $this->getContainer()->get(FileMediaUploadValidator::class);
    }

    public function testUploadFile(): void
    {
        $file = $this->getUploadFixture('empty.pdf');
        $this->validator->validate($file);
        static::assertTrue(true);
    }

    public function testUploadImage(): void
    {
        $file = $this->getUploadFixture('image.png');
        self::expectException(FileTypeNotAllowedException::class);

        $this->validator->validate($file);
    }

    public function testUploadDocumentWithInvalidMimeType(): void
    {
        $file = new UploadedFile(self::FIXTURE_DIR . '/image.png', 'empty.pdf', 'image/pdf', null, true);
        self::expectException(FileTypeNotAllowedException::class);

        $this->validator->validate($file);
    }

    private function getUploadFixture(string $filename): UploadedFile
    {
        return new UploadedFile(self::FIXTURE_DIR . '/' . $filename, $filename, null, null, true);
    }
}
