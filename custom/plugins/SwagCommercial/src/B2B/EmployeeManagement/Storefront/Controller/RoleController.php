<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Storefront\Controller;

use Shopware\Commercial\B2B\EmployeeManagement\Api\Controller\AbstractRoleRoute;
use Shopware\Commercial\B2B\EmployeeManagement\Domain\Permission\BaseEmployeePermissions;
use Shopware\Commercial\B2B\EmployeeManagement\EmployeeManagement;
use Shopware\Commercial\B2B\EmployeeManagement\Entity\BusinessPartner\BusinessPartnerEntity;
use Shopware\Commercial\B2B\EmployeeManagement\Storefront\Page\Role\Detail\RoleDetailPageLoader;
use Shopware\Commercial\B2B\EmployeeManagement\Storefront\Page\Role\List\RoleListPageLoader;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Validation\Exception\ConstraintViolationException;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @internal
 */
#[Route(
    defaults: ['_routeScope' => ['storefront'], '_b2bFeatureCode' => EmployeeManagement::FEATURE_CODE]
)]
#[Package('checkout')]
class RoleController extends StorefrontController
{
    public function __construct(
        private readonly RoleListPageLoader $roleListPageLoader,
        private readonly RoleDetailPageLoader $roleDetailPageLoader,
        private readonly AbstractRoleRoute $roleRoute,
    ) {
    }

    #[Route(
        path: '/account/business-partner/roles',
        name: 'frontend.business-partner.roles.list',
        defaults: ['XmlHttpRequest' => true, '_b2bEmployeeCan' => [BaseEmployeePermissions::ROLE_READ]],
        methods: ['GET', 'POST'],
        condition: 'service(\'license\').check(\'EMPLOYEE_MANAGEMENT-4838834\')'
    )]
    public function roleList(Request $request, SalesChannelContext $context, BusinessPartnerEntity $businessPartner): Response
    {
        $page = $this->roleListPageLoader->load($request, $context, $businessPartner);

        return $this->renderStorefront('@Commercial/storefront/page/account/b2b/roles.html.twig', [
            'page' => $page,
        ]);
    }

    #[Route(
        path: '/account/business-partner/roles/new',
        name: 'frontend.business-partner.roles.create',
        defaults: ['_b2bEmployeeCan' => [BaseEmployeePermissions::ROLE_CREATE]],
        methods: ['GET'],
        condition: 'service(\'license\').check(\'EMPLOYEE_MANAGEMENT-4838834\')'
    )]
    public function roleCreate(Request $request, SalesChannelContext $context, BusinessPartnerEntity $businessPartner): Response
    {
        $page = $this->roleDetailPageLoader->load(null, $request, $context, $businessPartner);

        return $this->renderStorefront('@Commercial/storefront/page/account/b2b/role-create.html.twig', [
            'page' => $page,
            'formViolations' => $request->attributes->get('formViolations'),
        ]);
    }

    #[Route(
        path: '/account/business-partner/roles/new',
        name: 'frontend.business-partner.roles.create.submit',
        defaults: ['_b2bEmployeeCan' => [BaseEmployeePermissions::ROLE_CREATE]],
        methods: ['POST'],
        condition: 'service(\'license\').check(\'EMPLOYEE_MANAGEMENT-4838834\')'
    )]
    public function createNewRole(Request $request, SalesChannelContext $context, BusinessPartnerEntity $businessPartner): Response
    {
        try {
            $this->roleRoute->create($request, $context, $businessPartner);

            $this->addFlash(self::SUCCESS, $this->trans('employee-management.flash.role.createSuccess'));

            return $this->redirectToRoute('frontend.business-partner.roles.list');
        } catch (ConstraintViolationException $formViolations) {
            $request->attributes->set('formViolations', $formViolations);

            return $this->roleCreate($request, $context, $businessPartner);
        }
    }

    #[Route(
        path: '/account/business-partner/roles/detail/{id}',
        name: 'frontend.business-partner.roles.detail',
        defaults: ['_b2bEmployeeCan' => [BaseEmployeePermissions::ROLE_READ]],
        methods: ['GET'],
        condition: 'service(\'license\').check(\'EMPLOYEE_MANAGEMENT-4838834\')'
    )]
    public function roleDetail(string $id, Request $request, SalesChannelContext $context, BusinessPartnerEntity $businessPartner): Response
    {
        $page = $this->roleDetailPageLoader->load($id, $request, $context, $businessPartner);

        return $this->renderStorefront('@Commercial/storefront/page/account/b2b/role-edit.html.twig', [
            'page' => $page,
        ]);
    }

    #[Route(
        path: '/account/business-partner/roles/detail/{id}',
        name: 'frontend.business-partner.roles.detail.submit',
        defaults: ['_b2bEmployeeCan' => [BaseEmployeePermissions::ROLE_EDIT]],
        methods: ['POST'],
        condition: 'service(\'license\').check(\'EMPLOYEE_MANAGEMENT-4838834\')'
    )]
    public function editRole(string $id, Request $request, SalesChannelContext $context, BusinessPartnerEntity $businessPartner): Response
    {
        try {
            $this->roleRoute->edit($id, $request, $context, $businessPartner);

            $this->addFlash(self::SUCCESS, $this->trans('employee-management.flash.role.updateSuccess', [
                '%name%' => $request->get('name'),
            ]));

            return $this->redirectToRoute('frontend.business-partner.roles.list');
        } catch (ConstraintViolationException $formViolations) {
            $page = $this->roleDetailPageLoader->load($id, $request, $context, $businessPartner);

            return $this->renderStorefront('@Commercial/storefront/page/account/b2b/role-edit.html.twig', [
                'page' => $page,
                'formViolations' => $formViolations,
            ]);
        }
    }

    #[Route(
        path: '/account/business-partner/roles/delete/{id}',
        name: 'frontend.business-partner.roles.delete.submit',
        defaults: ['XmlHttpRequest' => true, '_b2bEmployeeCan' => [BaseEmployeePermissions::ROLE_DELETE]],
        methods: ['DELETE'],
        condition: 'service(\'license\').check(\'EMPLOYEE_MANAGEMENT-4838834\')'
    )]
    public function deleteRole(string $id, Request $request, SalesChannelContext $context, BusinessPartnerEntity $businessPartner): Response
    {
        $this->roleRoute->delete($id, $context, $businessPartner);
        $request->request->set('redirectTo', 'frontend.business-partner.roles.list');

        $this->addFlash(self::SUCCESS, $this->trans('employee-management.flash.role.deleteSuccess'));

        return $this->createActionResponse($request);
    }
}
