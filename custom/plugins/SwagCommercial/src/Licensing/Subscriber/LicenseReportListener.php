<?php declare(strict_types=1);

namespace Shopware\Commercial\Licensing\Subscriber;

use Shopware\Commercial\Licensing\LicenseReporter;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * @internal
 */
#[Package('merchant-services')]
final class LicenseReportListener
{
    /**
     * @internal
     */
    public function __construct(private readonly LicenseReporter $reporter)
    {
    }

    public function __invoke(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if ($request->headers->has('sw-license-toggle')) {
            $this->reporter->reportNow((string) $request->headers->get('sw-license-toggle'));

            $event->setResponse(new Response('', Response::HTTP_NO_CONTENT));
        }

        if ($request->attributes->get('_route') !== 'api.action.store.plugin.search') {
            return;
        }

        $this->reporter->report();
    }
}
