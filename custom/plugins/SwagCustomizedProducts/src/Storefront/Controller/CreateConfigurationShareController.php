<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Storefront\Controller;

use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Swag\CustomizedProducts\Template\Aggregate\TemplateConfiguration\Route\ConfigurationShareCreatedResponse;
use Swag\CustomizedProducts\Template\Aggregate\TemplateConfiguration\Route\CreateConfigurationShareRoute;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"storefront"}})
 */
class CreateConfigurationShareController extends StorefrontController
{
    public function __construct(protected CreateConfigurationShareRoute $createConfigurationShareRoute)
    {
    }

    /**
     * @Route("/customized-products/config/create-share", name="frontend.customized-products.configuration.create-share", methods={"POST"}, defaults={"XmlHttpRequest"=true})
     */
    public function createShare(Request $request, SalesChannelContext $context): ConfigurationShareCreatedResponse
    {
        return $this->createConfigurationShareRoute->createConfigurationShare($request, $context);
    }
}
