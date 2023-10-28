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
class CsvReader
{
    private const FEATURE_TOGGLE_FOR_SERVICE = 'QUICK_ORDER-7355963';

    private const DELIMITER = ',';

    /**
     * @return array<string, int>
     */
    public function read(UploadedFile $file): array
    {
        if (!License::get(self::FEATURE_TOGGLE_FOR_SERVICE)) {
            throw AccountQuickOrderException::licenseExpired();
        }

        $dataCsv = [];

        // @codeCoverageIgnoreStart
        if (!($handle = fopen($file->getPathname(), 'rb'))) {
            return $dataCsv;
        }
        // @codeCoverageIgnoreEnd

        $headers = fgetcsv($handle, 0, self::DELIMITER);

        if (!$headers || !\in_array('product_number', $headers, true) || !\in_array('quantity', $headers, true)) {
            throw AccountQuickOrderException::invalidFile();
        }

        while (($row = fgetcsv($handle, 0, self::DELIMITER)) !== false) {
            $productNumber = (string) $row[0];

            if (empty($productNumber) || !\array_key_exists(1, $row)) {
                continue;
            }

            $quantity = (int) $row[1];

            $dataCsv[$productNumber] = ($dataCsv[$productNumber] ?? 0) + $quantity;
        }

        fclose($handle);

        return $dataCsv;
    }
}
