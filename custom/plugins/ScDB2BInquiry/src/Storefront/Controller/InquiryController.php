<?php

declare(strict_types=1);

namespace ScDB2BInquiry\Storefront\Controller;

use Shopware\Core\Content\Mail\Service\AbstractMailService;
use Shopware\Core\Content\MailTemplate\MailTemplateEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Validation\DataBag\DataBag;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route(defaults={"_routeScope" = {"storefront"}})
 */
class InquiryController extends StorefrontController
{
    //  TODO: use abstract Mailservice?
    public function __construct(
        private readonly EntityRepository $mailTemplateRepository,
        private readonly AbstractMailService $mailService,
        private readonly SystemConfigService $config,
        private readonly TranslatorInterface $translator
    ) {
    }

    #[Route(path: '/inquiry/addproduct', name: 'frontend.inquiry.add', methods: ['POST'], defaults: ['XmlHttpRequest' => true])]
    public function addInquiryProductForm(Request $request): Response
    {
        $session = $request->getSession();

        $inquirySessionArray = $session->get("scdInquiryBasket");
        $inquiryQuantity = $request->get("quantity");
        $inquiryProductNumber = $request->get("productNumber");
        $inquiryProductId = $request->get("id");
        $inquiryProductLabel = $request->get("label");
        $inquiryProductMediaId = $request->get("mediaId");

        if ($inquirySessionArray != null) {
            $inquirySessionArray[$inquiryProductNumber] = [
                "id" => $inquiryProductId,
                "label" => $inquiryProductLabel,
                "productNumber" => $inquiryProductNumber,
                "quantity" => $inquiryQuantity,
                "mediaId" => $inquiryProductMediaId
            ];
        } else {
            $inquirySessionArray = [];
            $inquirySessionArray[$inquiryProductNumber] = [
                "id" => $inquiryProductId,
                "label" => $inquiryProductLabel,
                "productNumber" => $inquiryProductNumber,
                "quantity" => $inquiryQuantity,
                "mediaId" => $inquiryProductMediaId
            ];
        }

        $session->set("scdInquiryBasket", $inquirySessionArray);

        $this->addFlash('success', $this->translator->trans('scd.inquiryAddSuccess'));

        return $this->createActionResponse($request);
    }

    #[Route(path: '/inquiry/removeproduct', name: 'frontend.inquiry.remove', methods: ['POST'], defaults: ['XmlHttpRequest' => true])]
    public function removeInquiryProductForm(Request $request): RedirectResponse
    {
        $session = $request->getSession();

        $currentInquirySession = $session->get("scdInquiryBasket");
        $inquiryProductNumber = $request->get("productNumber");

        unset($currentInquirySession[$inquiryProductNumber]);

        $session->set("scdInquiryBasket", $currentInquirySession);

        $this->addFlash('success', $this->translator->trans('scd.inquiryRemoveSuccess'));

        return new RedirectResponse("/checkout/cart");
    }

    #[Route(path: '/inquiry/changequantity', name: 'frontend.inquiry.changequantity', methods: ['POST'], defaults: ['XmlHttpRequest' => true])]
    public function changeInquiryProductQuantityForm(Request $request): RedirectResponse
    {
        $session = $request->getSession();

        $inquirySessionArray = $session->get("scdInquiryBasket");
        $inquiryQuantity = $request->get("quantity");
        $inquiryProductNumber = $request->get("productNumber");
        $inquiryProductId = $request->get("id");
        $inquiryProductLabel = $request->get("label");
        $inquiryProductMediaId = $request->get("mediaId");

        $inquirySessionArray[$inquiryProductNumber] = [
            "id" => $inquiryProductId,
            "label" => $inquiryProductLabel,
            "productNumber" => $inquiryProductNumber,
            "quantity" => $inquiryQuantity,
            "mediaId" => $inquiryProductMediaId
        ];

        $session->set("scdInquiryBasket", $inquirySessionArray);

        $this->addFlash('success', $this->translator->trans('scd.inquiryUpdateQuantity'));

        return new RedirectResponse("/checkout/cart");
    }

    #[Route(path: '/form/inquiry', name: 'frontend.inquiry.send', methods: ['POST'], defaults: ['XmlHttpRequest' => true])]
    public function sendInquiryForm(Request $request, RequestDataBag $data, SalesChannelContext $saleschannelContext): Response
    {
        $context = $saleschannelContext->getContext();

        $criteria = new Criteria();
        $criteria->addAssociation('mail_template_type.technicalName');
        $criteria->addFilter(new EqualsFilter('mailTemplateType.technicalName', 'scd.inquiryform'));
        $mailTemplate = $this->mailTemplateRepository->search($criteria, $context)->first();

        $inquiryProductCount = $data->get("inquiryProductsCount");
        $inquiryProducts = [];

        for ($i = 1; $i <= $inquiryProductCount; $i++) {
            $inquiryProducts[] = [
                "productNumber" =>   $data->get("productNumber" . $i),
                "productLabel" =>   $data->get("productLabel" . $i),
                "productQuantity" =>   $data->get("productQuantity" . $i)
            ];
        }
        if ($this->config->get('ScDB2BInquiry.config.scdMailreceiver', $saleschannelContext->getSalesChannelId())) {
            $session = $request->getSession();

            $this->sendMail($data, $saleschannelContext, $mailTemplate, $inquiryProducts, $this->config->get('ScDB2BInquiry.config.scdMailreceiver', $saleschannelContext->getSalesChannelId()));
            $session->set("scdInquiryBasket", "");

            $this->addFlash('success', $this->translator->trans('scd.inquiryFormSuccess'));
            $this->addFlash('success', $this->translator->trans('scd.inquiryRemoveSuccess'));
        } else {
            $this->addFlash('danger', $this->translator->trans('scd.missingEmailError'));
        }


        return $this->createActionResponse($request);
    }

    private function sendMail(RequestDataBag $formData, SalesChannelContext $saleschannelContext, MailTemplateEntity $mailTemplate, array $inquiryProducts, string $mailreceiver = null): void
    {
        $context = $saleschannelContext->getContext();
        $data = new DataBag();
        $data->set(
            'recipients',
            [
                $mailreceiver => 'Admin',
            ]
        );
        $data->set('senderName', $formData->get('firstName') . ' ' . $formData->get('lastName'));
        $data->set('salesChannelId', $saleschannelContext->getSalesChannel()->getId());

        $data->set('contentHtml', $mailTemplate->getContentHtml());
        $data->set('contentPlain', $mailTemplate->getContentPlain());
        $data->set('subject', $mailTemplate->getSubject());

        $customformData = [
            'firstName' => $formData->get('firstName'),
            'lastName' => $formData->get('lastName'),
            'phone' => $formData->get('phone'),
            'email' => $formData->get('email'),
            'company' => $formData->get('company'),
            'department' => $formData->get('department'),
            'customernumber' => $formData->get('customernumber'),
            'comment' => $formData->get('comment'),
            'products' => $inquiryProducts
        ];

        $this->mailService->send(
            $data->all(),
            $context,
            [
                'data' => $customformData,
            ]
        );
    }

    protected function createActionResponse(Request $request): Response
    {
        if ($request->get('redirectTo')) {
            $params = $this->decodeParam($request, 'redirectParameters');

            return $this->redirectToRoute($request->get('redirectTo'), $params);
        }

        if ($request->get('forwardTo')) {
            $params = $this->decodeParam($request, 'forwardParameters');

            return $this->forwardToRoute($request->get('forwardTo'), [], $params);
        }

        return new Response();
    }
}
