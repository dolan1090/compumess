import { LICENSE_TRAP_PRIMARY, LICENSE_TRAP_SECONDARY } from '../../constants';

type AdvancedSearchLicense = typeof LICENSE_TRAP_PRIMARY | typeof LICENSE_TRAP_SECONDARY;

/**
 * License decorator helper
 *
 * @private
 *
 * @package buyers-experience
 */
export default function licenseDecoratorHelper(repository, license: AdvancedSearchLicense) {
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
