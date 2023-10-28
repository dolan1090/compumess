<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\QuickOrder\Domain\File;

use Shopware\Commercial\B2B\QuickOrder\Exception\AccountQuickOrderException;
use Shopware\Commercial\Licensing\License;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @internal
 */
#[Package('checkout')]
class CsvDeleter
{
    private const FEATURE_TOGGLE_FOR_SERVICE = 'QUICK_ORDER-7355963';

    public function delete(UploadedFile $file): void
    {
        if (!License::get(self::FEATURE_TOGGLE_FOR_SERVICE)) {
            throw AccountQuickOrderException::licenseExpired();
        }

        $pathName = $file->getPathname();
        if (!file_exists($pathName)) {
            return;
        }

        unlink($pathName);
    }
}
