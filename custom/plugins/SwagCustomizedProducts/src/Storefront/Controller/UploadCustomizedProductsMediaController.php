<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Storefront\Controller;

use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Swag\CustomizedProducts\Storefront\Upload\UploadCustomizedProductsMediaRoute;
use Swag\CustomizedProducts\Storefront\Upload\UploadCustomizedProductsMediaRouteResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"storefront"}})
 */
class UploadCustomizedProductsMediaController extends StorefrontController
{
    public function __construct(protected UploadCustomizedProductsMediaRoute $uploadCustomizedProductsMediaRoute)
    {
    }

    /**
     * @Route("/customized-products/media/upload", name="frontend.customized-products.media.upload", methods={"POST"}, defaults={"XmlHttpRequest"=true})
     */
    public function upload(Request $request, SalesChannelContext $context): UploadCustomizedProductsMediaRouteResponse
    {
        return $this->uploadCustomizedProductsMediaRoute->upload($request, $context);
    }
}
