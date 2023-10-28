<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Storefront\Controller;

use Shopware\Commercial\B2B\EmployeeManagement\Api\Controller\AbstractEmployeeRoute;
use Shopware\Commercial\B2B\EmployeeManagement\Domain\Permission\BaseEmployeePermissions;
use Shopware\Commercial\B2B\EmployeeManagement\Domain\Recovery\AbstractEmployeeRecoveryIsExpiredRoute;
use Shopware\Commercial\B2B\EmployeeManagement\Domain\Recovery\EmployeeRecoverPasswordPageLoader;
use Shopware\Commercial\B2B\EmployeeManagement\Domain\Registration\AbstractEmployeeConfirmPasswordRoute;
use Shopware\Commercial\B2B\EmployeeManagement\EmployeeManagement;
use Shopware\Commercial\B2B\EmployeeManagement\Entity\BusinessPartner\BusinessPartnerEntity;
use Shopware\Commercial\B2B\EmployeeManagement\Exception\EmployeeManagementException;
use Shopware\Commercial\B2B\EmployeeManagement\Storefront\Page\Employee\Detail\EmployeeDetailPageLoader;
use Shopware\Commercial\B2B\EmployeeManagement\Storefront\Page\Employee\List\EmployeeListPageLoader;
use Shopware\Core\Checkout\Customer\Exception\CustomerNotFoundByHashException;
use Shopware\Core\Checkout\Customer\Exception\CustomerRecoveryHashExpiredException;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Routing\RoutingException;
use Shopware\Core\Framework\Validation\DataBag\DataBag;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\Framework\Validation\Exception\ConstraintViolationException;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Storefront\Framework\Routing\RequestTransformer;
use Shopware\Storefront\Page\Account\Profile\AccountProfilePageLoader;
use Shopware\Storefront\Page\Account\RecoverPassword\AccountRecoverPasswordPage;
use Shopware\Storefront\Page\Account\RecoverPassword\AccountRecoverPasswordPageLoadedHook;
use Shopware\Storefront\Page\GenericPageLoaderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @internal
 */
#[Route(defaults: ['_routeScope' => ['storefront']])]
#[Package('checkout')]
class BusinessPartnerAccountController extends StorefrontController
{
    public function __construct(
        private readonly EmployeeListPageLoader $employeeListPageLoader,
        private readonly EmployeeDetailPageLoader $employeeDetailPageLoader,
        private readonly AbstractEmployeeRoute $employeeRoute,
        private readonly AccountProfilePageLoader $accountProfilePageLoader,
        private readonly AbstractEmployeeRecoveryIsExpiredRoute $isEmployeeRecoveryExpiredRoute,
        private readonly GenericPageLoaderInterface $genericLoader,
        private readonly AbstractEmployeeConfirmPasswordRoute $confirmPasswordRoute,
        private readonly EmployeeRecoverPasswordPageLoader $employeeRecoverPasswordPageLoader,
    ) {
    }

    #[Route(
        path: '/account/business-partner/employee/recover/password/{hash}',
        name: 'frontend.business-partner.employee.recover.password.page',
        methods: ['GET']
    )]
    public function employeePasswordRecoverForm(string $hash, Request $request, SalesChannelContext $context): Response
    {
        try {
            $page = $this->employeeRecoverPasswordPageLoader->load($request, $context, $hash);
        } catch (ConstraintViolationException) {
            $this->addFlash(self::DANGER, $this->trans('account.passwordHashNotFound'));

            return $this->redirectToRoute('frontend.account.recover.request');
        }

        $this->hook(new AccountRecoverPasswordPageLoadedHook($page, $context));

        if ($page->getHash() === null || $page->isHashExpired()) {
            $this->addFlash(self::DANGER, $this->trans('account.passwordHashNotFound'));

            return $this->redirectToRoute('frontend.account.recover.request');
        }

        return $this->renderStorefront('@Commercial/storefront/page/account/b2b/reset-password.html.twig', [
            'page' => $page,
            'formViolations' => $request->get('formViolations'),
        ]);
    }

    #[Route(
        path: '/account/business-partner/employee/recover/password',
        name: 'frontend.account.business-partner.employee.recover.password.reset',
        methods: ['POST']
    )]
    public function resetPassword(RequestDataBag $data, SalesChannelContext $context): Response
    {
        $passwordData = $data->get('password');
        if (!$passwordData instanceof DataBag) {
            throw RoutingException::invalidRequestParameter('password');
        }
        $hash = $passwordData->get('hash');

        try {
            $this->confirmPasswordRoute->confirmPassword($passwordData->toRequestDataBag(), $context);

            $this->addFlash(self::SUCCESS, $this->trans('account.passwordChangeSuccess'));
        } catch (ConstraintViolationException $formViolations) {
            if ($formViolations->getViolations('newPassword')->count() === 1) {
                $this->addFlash(self::DANGER, $this->trans('account.passwordNotIdentical'));
            } else {
                $this->addFlash(self::DANGER, $this->trans('account.passwordChangeNoSuccess'));
            }

            return $this->forwardToRoute(
                'frontend.business-partner.employee.recover.password.page',
                ['formViolations' => $formViolations, 'passwordFormViolation' => true],
                ['hash' => $hash],
            );
        } catch (CustomerNotFoundByHashException) {
            $this->addFlash(self::DANGER, $this->trans('account.passwordChangeNoSuccess'));

            return $this->forwardToRoute('frontend.account.recover.request');
        } catch (CustomerRecoveryHashExpiredException) {
            $this->addFlash(self::DANGER, $this->trans('account.passwordHashExpired'));

            return $this->forwardToRoute('frontend.account.recover.request');
        }

        return $this->redirectToRoute('frontend.account.profile.page');
    }

    #[Route(
        path: '/account/business-partner/employee/invite/{hash}',
        name: 'frontend.business-partner.employee.invite.page',
        methods: ['GET']
    )]
    public function employeeInviteForm(string $hash, Request $request, SalesChannelContext $context): Response
    {
        try {
            $response = $this->isEmployeeRecoveryExpiredRoute->load(new RequestDataBag(['hash' => $hash]), $context);
        } catch (EmployeeManagementException) {
            $this->addFlash(self::DANGER, $this->trans('employee-management.employeeSetPassword.employeeWithHashNotFound'));

            return $this->redirectToRoute('frontend.account.login.page');
        }

        if ($response->isExpired()) {
            $this->addFlash(self::DANGER, $this->trans('employee-management.employeeSetPassword.employeeWithHashNotFound'));

            return $this->redirectToRoute('frontend.account.login.page');
        }

        $page = $this->genericLoader->load($request, $context);
        $page = AccountRecoverPasswordPage::createFrom($page);
        $page->setHash($hash);
        $page->setHashExpired($response->isExpired());

        return $this->renderStorefront('@Commercial/storefront/page/account/b2b/confirm-invite.html.twig', [
            'page' => $page,
            'formViolations' => $request->get('formViolations'),
        ]);
    }

    #[Route(
        path: '/account/business-partner/employee/invite/confirm',
        name: 'frontend.business-partner.employee.invite.confirm',
        methods: ['POST']
    )]
    public function confirmInvite(RequestDataBag $data, SalesChannelContext $context): Response
    {
        $passwordData = $data->get('password');
        if (!$passwordData instanceof DataBag) {
            throw RoutingException::invalidRequestParameter('password');
        }
        $hash = $passwordData->get('hash');

        try {
            $this->confirmPasswordRoute->confirmPassword($passwordData->toRequestDataBag(), $context);

            $this->addFlash(self::SUCCESS, $this->trans('employee-management.employeeSetPassword.inviteConfirmSuccess'));
        } catch (ConstraintViolationException $formViolations) {
            if ($formViolations->getViolations('newPassword')->count() === 1) {
                $this->addFlash(self::DANGER, $this->trans('account.passwordNotIdentical'));
            } else {
                $this->addFlash(self::DANGER, $this->trans('employee-management.employeeSetPassword.inviteConfirmNoSuccess'));
            }

            return $this->forwardToRoute(
                'frontend.business-partner.employee.invite.page',
                ['formViolations' => $formViolations, 'passwordFormViolation' => true],
                ['hash' => $hash],
            );
        } catch (EmployeeManagementException $b2bException) {
            $this->addFlash(self::DANGER, $this->trans('employee-management.employeeSetPassword.inviteConfirmNoSuccess'));

            return $this->forwardToRoute('frontend.account.login.page');
        }

        return $this->redirectToRoute('frontend.account.profile.page');
    }

    #[Route(
        path: '/account/business-partner/employees',
        name: 'frontend.business-partner.employees.list',
        defaults: [
            'XmlHttpRequest' => true,
            '_b2bEmployeeCan' => [BaseEmployeePermissions::EMPLOYEE_READ],
            '_b2bFeatureCode' => EmployeeManagement::FEATURE_CODE,
        ],
        methods: ['GET', 'POST'],
        condition: 'service(\'license\').check(\'EMPLOYEE_MANAGEMENT-4838834\')'
    )]
    public function employeeList(Request $request, SalesChannelContext $context, BusinessPartnerEntity $businessPartner): Response
    {
        $page = $this->employeeListPageLoader->load($request, $context, $businessPartner);

        if ($request->query->get('deactivate')) {
            $this->addFlash(self::SUCCESS, $this->trans('employee-management.flash.employee.deactivateSuccess', [
                '%firstName%' => $request->query->get('firstName'),
                '%lastName%' => $request->query->get('lastName'),
            ]));
        }

        return $this->renderStorefront('@Commercial/storefront/page/account/b2b/employees.html.twig', [
            'page' => $page,
        ]);
    }

    #[Route(
        path: '/account/business-partner/employees/new',
        name: 'frontend.business-partner.employees.create',
        defaults: [
            '_b2bEmployeeCan' => [BaseEmployeePermissions::EMPLOYEE_CREATE],
            '_b2bFeatureCode' => EmployeeManagement::FEATURE_CODE,
        ],
        methods: ['GET'],
        condition: 'service(\'license\').check(\'EMPLOYEE_MANAGEMENT-4838834\')'
    )]
    public function employeeCreate(Request $request, SalesChannelContext $context, BusinessPartnerEntity $businessPartner): Response
    {
        return $this->renderStorefront('@Commercial/storefront/page/account/b2b/employee-create.html.twig', [
            'page' => $this->employeeDetailPageLoader->loadCreate($request, $context, $businessPartner),
            'formViolations' => $request->attributes->get('formViolations'),
        ]);
    }

    #[Route(
        path: '/account/business-partner/employees/new',
        name: 'frontend.business-partner.employees.create.submit',
        defaults: [
            '_b2bEmployeeCan' => [BaseEmployeePermissions::EMPLOYEE_CREATE],
            '_b2bFeatureCode' => EmployeeManagement::FEATURE_CODE,
        ],
        methods: ['POST'],
        condition: 'service(\'license\').check(\'EMPLOYEE_MANAGEMENT-4838834\')'
    )]
    public function createNewEmployee(Request $request, SalesChannelContext $context, BusinessPartnerEntity $businessPartner): Response
    {
        $storefrontUrl = $request->attributes->get(RequestTransformer::STOREFRONT_URL);
        $request->request->set('storefrontUrl', \is_string($storefrontUrl) ? $storefrontUrl : null);

        try {
            $this->employeeRoute->create($request, $context, $businessPartner);

            $this->addFlash(self::SUCCESS, $this->trans('employee-management.flash.employee.createSuccess'));

            return $this->redirectToRoute('frontend.business-partner.employees.list');
        } catch (ConstraintViolationException $formViolations) {
            $request->attributes->set('formViolations', $formViolations);

            return $this->employeeCreate($request, $context, $businessPartner);
        }
    }

    #[Route(
        path: '/account/business-partner/employees/detail/{id}',
        name: 'frontend.business-partner.employees.detail',
        defaults: [
            '_b2bEmployeeCan' => [BaseEmployeePermissions::EMPLOYEE_READ],
            '_b2bFeatureCode' => EmployeeManagement::FEATURE_CODE,
        ],
        methods: ['GET'],
        condition: 'service(\'license\').check(\'EMPLOYEE_MANAGEMENT-4838834\')'
    )]
    public function employeeDetail(string $id, Request $request, SalesChannelContext $context, BusinessPartnerEntity $businessPartner): Response
    {
        $page = $this->employeeDetailPageLoader->load($id, $request, $context, $businessPartner);

        return $this->renderStorefront('@Commercial/storefront/page/account/b2b/employee-edit.html.twig', [
            'page' => $page,
        ]);
    }

    #[Route(
        path: '/account/employee/profile/{id}',
        name: 'frontend.employee.profile.submit',
        defaults: [
            '_b2bEmployeeCan' => [BaseEmployeePermissions::EMPLOYEE_EDIT],
            '_b2bFeatureCode' => EmployeeManagement::FEATURE_CODE,
        ],
        methods: ['POST'],
        condition: 'service(\'license\').check(\'EMPLOYEE_MANAGEMENT-4838834\')'
    )]
    public function employeeProfile(string $id, Request $request, SalesChannelContext $context, BusinessPartnerEntity $businessPartner): Response
    {
        try {
            $this->employeeRoute->edit($id, $request, $context, $businessPartner);
            $this->addFlash(self::SUCCESS, $this->trans('employee-management.flash.employee.editSuccess', [
                '%firstName%' => $request->get('firstName'),
                '%lastName%' => $request->get('lastName'),
            ]));

            return $this->redirectToRoute('frontend.account.profile.page');
        } catch (ConstraintViolationException $formViolations) {
            $page = $this->accountProfilePageLoader->load($request, $context);

            return $this->renderStorefront('@Shopware/storefront/page/account/profile/index.html.twig', [
                'page' => $page,
                'formViolations' => $formViolations,
            ]);
        }
    }

    #[Route(
        path: '/account/business-partner/employees/detail/{id}',
        name: 'frontend.business-partner.employees.detail.submit',
        defaults: [
            '_b2bEmployeeCan' => [BaseEmployeePermissions::EMPLOYEE_EDIT],
            '_b2bFeatureCode' => EmployeeManagement::FEATURE_CODE,
        ],
        methods: ['POST'],
        condition: 'service(\'license\').check(\'EMPLOYEE_MANAGEMENT-4838834\')'
    )]
    public function editEmployee(string $id, Request $request, SalesChannelContext $context, BusinessPartnerEntity $businessPartner): Response
    {
        try {
            $this->employeeRoute->edit($id, $request, $context, $businessPartner);
            $this->addFlash(self::SUCCESS, $this->trans('employee-management.flash.employee.editSuccess', [
                '%firstName%' => $request->get('firstName'),
                '%lastName%' => $request->get('lastName'),
            ]));

            return $this->redirectToRoute('frontend.business-partner.employees.list');
        } catch (ConstraintViolationException $formViolations) {
            $page = $this->employeeDetailPageLoader->load($id, $request, $context, $businessPartner);

            return $this->renderStorefront('@Commercial/storefront/page/account/b2b/employee-edit.html.twig', [
                'page' => $page,
                'formViolations' => $formViolations,
            ]);
        }
    }

    #[Route(
        path: '/account/business-partner/employees/reinvite/{id}',
        name: 'frontend.business-partner.employees.reinvite',
        defaults: [
            '_b2bEmployeeCan' => [BaseEmployeePermissions::EMPLOYEE_CREATE],
            '_b2bFeatureCode' => EmployeeManagement::FEATURE_CODE,
        ],
        methods: ['POST'],
        condition: 'service(\'license\').check(\'EMPLOYEE_MANAGEMENT-4838834\')'
    )]
    public function reinviteEmployee(string $id, Request $request, SalesChannelContext $context, BusinessPartnerEntity $businessPartner): Response
    {
        $storefrontUrl = $request->attributes->get(RequestTransformer::STOREFRONT_URL);
        $request->request->set('storefrontUrl', \is_string($storefrontUrl) ? $storefrontUrl : null);

        $this->employeeRoute->reinvite($id, $request, $context, $businessPartner);

        $this->addFlash(self::SUCCESS, $this->trans('employee-management.flash.employee.resendSuccess'));

        return $this->redirectToRoute('frontend.business-partner.employees.list');
    }

    #[Route(
        path: '/account/business-partner/employees/delete/{id}',
        name: 'frontend.business-partner.employees.delete.submit',
        defaults: [
            'XmlHttpRequest' => true,
            '_b2bEmployeeCan' => [BaseEmployeePermissions::EMPLOYEE_DELETE],
            '_b2bFeatureCode' => EmployeeManagement::FEATURE_CODE,
        ],
        methods: ['DELETE'],
        condition: 'service(\'license\').check(\'EMPLOYEE_MANAGEMENT-4838834\')'
    )]
    public function deleteEmployee(string $id, Request $request, SalesChannelContext $context, BusinessPartnerEntity $businessPartner): Response
    {
        $this->employeeRoute->delete($id, $context, $businessPartner);
        $request->request->set('redirectTo', 'frontend.business-partner.employees.list');

        $this->addFlash(self::SUCCESS, $this->trans('employee-management.flash.employee.deleteSuccess'));

        return $this->createActionResponse($request);
    }

    #[Route(
        path: '/account/business-partner/employees/deactivate/{id}',
        name: 'frontend.business-partner.employees.deactivate.submit',
        defaults: ['XmlHttpRequest' => true, '_loginRequired' => true, '_b2bEmployeeCan' => [BaseEmployeePermissions::EMPLOYEE_EDIT]],
        methods: ['GET'],
        condition: 'service(\'license\').check(\'EMPLOYEE_MANAGEMENT-4838834\')'
    )]
    public function deactivateEmployee(string $id, Request $request, SalesChannelContext $context, BusinessPartnerEntity $businessPartner): Response
    {
        $this->employeeRoute->deactivate($id, $context, $businessPartner);

        $request->request->set('redirectTo', 'frontend.business-partner.employees.list');

        return $this->createActionResponse($request);
    }

    #[Route(
        path: '/account/business-partner/employees/activate/{id}',
        name: 'frontend.business-partner.employees.activate.submit',
        defaults: ['XmlHttpRequest' => true, '_loginRequired' => true, '_b2bEmployeeCan' => [BaseEmployeePermissions::EMPLOYEE_EDIT]],
        methods: ['GET'],
        condition: 'service(\'license\').check(\'EMPLOYEE_MANAGEMENT-4838834\')'
    )]
    public function activateEmployee(string $id, Request $request, SalesChannelContext $context, BusinessPartnerEntity $businessPartner): Response
    {
        $result = $this->employeeRoute->activate($id, $context, $businessPartner);

        $request->request->set('redirectTo', 'frontend.business-partner.employees.list');

        $this->addFlash(self::SUCCESS, $this->trans('employee-management.flash.employee.activateSuccess', [
            '%firstName%' => $result->getEmployee()->getFirstName(),
            '%lastName%' => $result->getEmployee()->getLastName(),
        ]));

        return $this->createActionResponse($request);
    }
}
