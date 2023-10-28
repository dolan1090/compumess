<?php declare(strict_types=1);

namespace Shopware\Commercial\CheckoutSweetener\Domain\Checkout;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Struct\Struct;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

/**
 * @internal
 */
#[Package('checkout')]
class SweetenerResponse extends Struct
{
    protected string $text;

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): void
    {
        $this->text = $text;
    }

    public function assign(array $options)
    {
        $converter = new CamelCaseToSnakeCaseNameConverter();
        foreach ($options as $key => $value) {
            unset($options[$key]);
            $options[$converter->denormalize($key)] = $value;
        }

        return parent::assign($options);
    }
}
