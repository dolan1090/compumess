<?php declare(strict_types=1);

namespace Shopware\Commercial\CheckoutSweetener\SalesChannel;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\System\SalesChannel\StoreApiResponse;

/**
 * @final
 *
 * @internal
 */
#[Package('checkout')]
class CheckoutSweetenerResponse extends StoreApiResponse
{
    /**
     * @var ArrayStruct<string, string>
     */
    protected $object;

    public function __construct(string $text)
    {
        parent::__construct(new ArrayStruct(['text' => $text]));
    }

    public function getText(): string
    {
        /** @var string $message */
        $message = $this->object->get('text');

        return $message;
    }
}
