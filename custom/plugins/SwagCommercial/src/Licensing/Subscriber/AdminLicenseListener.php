<?php declare(strict_types=1);

namespace Shopware\Commercial\Licensing\Subscriber;

use Shopware\Commercial\Licensing\License;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
 * @internal
 */
#[Package('merchant-services')]
final class AdminLicenseListener
{
    public function __invoke(ResponseEvent $event): void
    {
        if ($event->getRequest()->attributes->get('_route') !== 'api.info.config') {
            return;
        }

        /** @var array{'version': string} $json */
        $json = json_decode((string) $event->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);

        $json['licenseToggles'] = License::all();

        $event->getResponse()->setContent(json_encode($json, \JSON_THROW_ON_ERROR));
    }
}
