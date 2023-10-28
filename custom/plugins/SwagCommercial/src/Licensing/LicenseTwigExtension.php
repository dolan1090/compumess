<?php declare(strict_types=1);

namespace Shopware\Commercial\Licensing;

use Shopware\Core\Framework\Log\Package;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

#[Package('merchant-services')]
class LicenseTwigExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('getLicense', $this->getLicense(...)),
        ];
    }

    /**
     * @return string|bool|int
     */
    public function getLicense(string $toggle)
    {
        return License::get($toggle);
    }
}
