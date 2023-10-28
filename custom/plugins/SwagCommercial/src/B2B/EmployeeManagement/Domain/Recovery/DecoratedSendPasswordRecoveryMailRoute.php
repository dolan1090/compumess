<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Domain\Recovery;

use Shopware\Commercial\B2B\EmployeeManagement\Domain\Validation\UrlValidator;
use Shopware\Commercial\B2B\EmployeeManagement\Entity\Employee\EmployeeEntity;
use Shopware\Commercial\B2B\EmployeeManagement\Exception\EmployeeManagementException;
use Shopware\Core\Checkout\Customer\Exception\CustomerNotFoundException;
use Shopware\Core\Checkout\Customer\SalesChannel\AbstractSendPasswordRecoveryMailRoute;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Util\Random;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SalesChannel\SuccessResponse;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 */
#[Route(defaults: ['_routeScope' => ['store-api'], '_contextTokenRequired' => true])]
#[Package('checkout')]
class DecoratedSendPasswordRecoveryMailRoute extends AbstractSendPasswordRecoveryMailRoute
{
    /**
     * @internal
     */
    public function __construct(
        private readonly AbstractSendPasswordRecoveryMailRoute $decorated,
        private readonly EntityRepository $employeeRepository,
        private readonly SystemConfigService $systemConfigService,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function getDecorated(): AbstractSendPasswordRecoveryMailRoute
    {
        return $this->decorated;
    }

    #[Route(path: '/store-api/account/recovery-password', name: 'store-api.account.recovery.send.mail', methods: ['POST'])]
    public function sendRecoveryMail(RequestDataBag $data, SalesChannelContext $context, bool $validateStorefrontUrl = true): SuccessResponse
    {
        try {
            return $this->decorated->sendRecoveryMail($data, $context, $validateStorefrontUrl);
        } catch (CustomerNotFoundException) {
            /** @var string $url */
            $url = $data->get('storefrontUrl');
            UrlValidator::validateStorefrontUrl($url, $context);

            $email = $data->get('email');
            if (!\is_string($email)) {
                throw EmployeeManagementException::invalidRequestArgument('Parameter email must be a string');
            }

            $criteria = new Criteria();
            $criteria->addFilter(new EqualsFilter('email', $email));
            $criteria->addFilter(new EqualsFilter('active', true));
            $criteria->addAssociation('businessPartnerCustomer.salutation');

            $employee = $this->employeeRepository->search($criteria, $context->getContext())->first();

            if (!$employee instanceof EmployeeEntity) {
                throw EmployeeManagementException::customerNotFoundByEmail($email);
            }

            $hash = Random::getAlphanumericString(32);
            $now = new \DateTimeImmutable();

            $this->employeeRepository->update([
                [
                    'id' => $employee->getId(),
                    'recoveryTime' => $now->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                    'recoveryHash' => $hash,
                ],
            ], $context->getContext());

            $employee->setRecoveryTime($now);
            $employee->setRecoveryHash($hash);

            $recoverUrl = $this->getRecoverUrl($context, $hash, $url);

            $event = new EmployeeAccountRecoverRequestEvent($context, $employee, $recoverUrl);
            $this->eventDispatcher->dispatch($event);

            return new SuccessResponse();
        }
    }

    private function getRecoverUrl(
        SalesChannelContext $context,
        string $hash,
        string $storefrontUrl
    ): string {
        $urlTemplate = $this->systemConfigService->get(
            'b2b.employee.invitationURL',
            $context->getSalesChannelId()
        );
        if (!\is_string($urlTemplate)) {
            $urlTemplate = '/account/business-partner/employee/recover/password/%%RECOVERHASH%%';
        }

        return rtrim($storefrontUrl, '/') . str_replace(
            '%%RECOVERHASH%%',
            $hash,
            $urlTemplate
        );
    }
}
