<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Storefront\Framework\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class CustomizedProductsTruncateEllipsisFilter extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('truncateEllipsis', $this->truncateEllipsis(...)),
            new TwigFilter('shouldTruncate', $this->shouldTruncate(...)),
        ];
    }

    /**
     * Checks if the provided string is long enough or has enough new line characters to be truncated
     */
    public function shouldTruncate(string $text, ?int $truncateChars = 100, ?int $truncateNewlines = 2): bool
    {
        $tooManyNewlines = \mb_substr_count($text, '\n') > $truncateNewlines && $truncateNewlines > -1;
        $tooManyChars = \mb_strlen($text) > $truncateChars && $truncateChars > -1;

        return $tooManyNewlines || $tooManyChars;
    }

    /**
     * Truncates the provided string to the given length and appends '...'
     */
    public function truncateEllipsis(string $text, ?int $oneLineChars = 10, ?string $hiddenText = '...'): string
    {
        if ($hiddenText === null) {
            $hiddenText = '...';
        }

        $firstLine = \explode('\n', $text, 1)[0];

        if ($oneLineChars > -1) {
            return \sprintf(
                '%s%s',
                \mb_substr($firstLine, 0, $oneLineChars),
                $hiddenText
            );
        }

        return \sprintf('%s%s', $firstLine, $hiddenText);
    }
}
