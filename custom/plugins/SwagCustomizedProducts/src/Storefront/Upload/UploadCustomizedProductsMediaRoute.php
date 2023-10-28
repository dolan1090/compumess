<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Storefront\Upload;

use OpenApi\Annotations as OA;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopware\Core\Framework\Routing\Exception\MissingRequestParameterException;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Framework\Media\Exception\FileTypeNotAllowedException;
use Shopware\Storefront\Framework\Media\Exception\MediaValidatorMissingException;
use Shopware\Storefront\Framework\Media\StorefrontMediaUploader;
use Swag\CustomizedProducts\Storefront\Framework\Media\Validator\AbstractMediaUploadValidator;
use Swag\CustomizedProducts\Storefront\Framework\Media\Validator\FileMediaUploadValidator;
use Swag\CustomizedProducts\Storefront\Framework\Media\Validator\ImageMediaUploadValidator;
use Swag\CustomizedProducts\Storefront\Upload\Exception\SwagCustomizedProductsInvalidExtensionException;
use Swag\CustomizedProducts\Storefront\Upload\Exception\SwagCustomizedProductsMaximumFileSizeExceededException;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Exception\InvalidOptionTypeException;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\TemplateOptionEntity;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\FileUpload;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\ImageUpload;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"store-api"}})
 */
class UploadCustomizedProductsMediaRoute extends AbstractUploadCustomizedProductsMediaRoute
{
    /**
     * @param iterable<AbstractMediaUploadValidator> $validators
     */
    public function __construct(
        private readonly EntityRepository $templateOptionRepository,
        private readonly StorefrontMediaUploader $storefrontMediaUploader,
        private readonly iterable $validators
    ) {
    }

    public function getDecorated(): AbstractUploadCustomizedProductsMediaRoute
    {
        throw new DecorationPatternException(self::class);
    }

    /**
     * @OA\Post(
     *     path="/customized-products/upload",
     *     description="Uploads a file for a custom product",
     *     operationId="uploadCustomizedProductCustomerFile",
     *     tags={"Store API", "Customized Products"},
     *
     *     @OA\Parameter(
     *         parameter="optionId",
     *         name="optionId",
     *         in="body",
     *         description="Id of the template option",
     *
     *         @OA\Schema(type="string", format="uuid"),
     *     ),
     *
     *     @OA\Parameter(
     *         parameter="file",
     *         name="file",
     *         in="body",
     *         description="The file to upload",
     *
     *         @OA\Schema(type="file"),
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *
     *         @OA\JsonContent(
     *             type="array",
     *
     *             @OA\Property(
     *                 property="mediaId",
     *                 type="string",
     *                 format="uuid"
     *             ),
     *             @OA\Property(
     *                 property="filename",
     *                 type="string"
     *             ),
     *             example={"mediaId": "19489f5e16e14ac8b7c1dad26a258923", "filename": "example.png"}
     *         )
     *     )
     * )
     *
     * @Route("/store-api/customized-products/upload", name="store-api.customized-products.upload", methods={"POST"})
     */
    public function upload(Request $request, SalesChannelContext $salesChannelContext): UploadCustomizedProductsMediaRouteResponse
    {
        $optionId = $request->request->getAlnum('optionId');
        if ($optionId === '') {
            throw new MissingRequestParameterException('optionId');
        }

        /** @var UploadedFile|null $file */
        $file = $request->files->get('file');
        if ($file === null) {
            throw new MissingRequestParameterException('file');
        }

        $context = $salesChannelContext->getContext();
        /** @var TemplateOptionEntity|null $option */
        $option = $this->templateOptionRepository->search(new Criteria([$optionId]), $context)->first();
        if ($option === null) {
            throw new MissingRequestParameterException('optionId');
        }

        $typeProperties = $option->getTypeProperties();
        if ($typeProperties === null) {
            throw new MediaValidatorMissingException('typeProperties');
        }
        $maxFileSize = $typeProperties['maxFileSize'];
        if ($file->getSize() > $maxFileSize * 1024 * 1024) {
            throw new SwagCustomizedProductsMaximumFileSizeExceededException();
        }

        $validator = $this->matchValidator($option->getType());

        try {
            $validator->checkExcludedFileTypes($file, $option);

            $mediaId = $this->storefrontMediaUploader->upload(
                $file,
                'swag_customized_products_template_storefront_upload',
                $validator->getType(),
                $salesChannelContext->getContext()
            );
        } catch (FileTypeNotAllowedException) {
            throw new SwagCustomizedProductsInvalidExtensionException();
        }

        return new UploadCustomizedProductsMediaRouteResponse($mediaId, $file->getClientOriginalName());
    }

    private function matchValidator(string $optionType): AbstractMediaUploadValidator
    {
        $validatorType = match ($optionType) {
            FileUpload::NAME => FileMediaUploadValidator::TYPE,
            ImageUpload::NAME => ImageMediaUploadValidator::TYPE,
            default => throw new InvalidOptionTypeException($optionType),
        };

        foreach ($this->validators as $validator) {
            if ($validator->getType() === $validatorType && $validator instanceof AbstractMediaUploadValidator) {
                return $validator;
            }
        }

        throw new MediaValidatorMissingException($validatorType);
    }
}
