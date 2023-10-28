type MultiWarehouseLicense =
    'MULTI_INVENTORY-2131206' |
    'MULTI_INVENTORY-2506228' |
    'MULTI_INVENTORY-3711815';

export default function licenseDecorator(repository, license: MultiWarehouseLicense) {
    if (!Shopware.License.get(license)) {
        return repository;
    }

    const originBuildHeaders = repository.buildHeaders;

    repository.buildHeaders = (context = Shopware.Context.api) => {
        const headers = originBuildHeaders.call(repository, context);

        Object.assign(headers, {
            'sw-license-toggle': license,
        });

        return headers;
    };

    return repository;
}
