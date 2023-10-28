<?php declare(strict_types=1);

namespace Shopware\Commercial\Licensing;

use Lcobucci\JWT\UnencryptedToken;
use Shopware\Commercial\Licensing\Exception\InvalidFeaturesException;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SystemConfig\SystemConfigService;

/**
 * @internal
 */
#[Package('merchant-services')]
class Features
{
    public const CONFIG_STORE_DISABLED_FEATURES = 'core.store.disabledFeatures';

    public const REGEX_FEATURE_NAME = '/^(?P<feature>[A-Z0-9_]+)-/';

    /**
     * @var array<Feature>
     */
    private array $allCommercialFeatures;

    /**
     * @internal
     *
     * @param array<Feature> $allCommercialFeatures
     */
    public function __construct(
        private SystemConfigService $systemConfig,
        array $allCommercialFeatures
    ) {
        $this->allCommercialFeatures = array_combine(
            array_map(fn (Feature $feature) => $feature->code, $allCommercialFeatures),
            $allCommercialFeatures
        );
    }

    /**
     * @param array<string> $features
     */
    public function disable(array $features): void
    {
        $this->validateFeatures($features);

        /** @var array<string> $disabledFeatures */
        $disabledFeatures = $this->systemConfig->get(self::CONFIG_STORE_DISABLED_FEATURES) ?? [];

        $this->systemConfig->set(
            self::CONFIG_STORE_DISABLED_FEATURES,
            array_unique([
                ...$disabledFeatures,
                ...$features,
            ])
        );
    }

    /**
     * @param array<string> $features
     */
    public function enable(array $features): void
    {
        $this->validateFeatures($features);

        /** @var array<string> $disabledFeatures */
        $disabledFeatures = $this->systemConfig->get(self::CONFIG_STORE_DISABLED_FEATURES) ?? [];

        $this->systemConfig->set(
            self::CONFIG_STORE_DISABLED_FEATURES,
            array_diff(
                $disabledFeatures,
                $features
            )
        );
    }

    public function isDisabled(string $feature): bool
    {
        return \in_array($feature, $this->getDisabled(), true);
    }

    public function isNotDisabled(string $feature): bool
    {
        return !\in_array($feature, $this->getDisabled(), true);
    }

    /**
     * Get all the not explicitly disabled feature toggles permitted by the given license
     *
     * @return array<string, string|int|boolean>
     */
    public function getAllEnabledToggles(UnencryptedToken $license): array
    {
        /** @var array<string, string|int|boolean> $toggles */
        $toggles = $license->claims()->get('license-toggles') ?? [];

        return array_filter(
            $toggles,
            function (mixed $value, string $toggle) {
                if (!preg_match(self::REGEX_FEATURE_NAME, $toggle, $matches)) {
                    // if it's not a feature name we recognise, assume enabled
                    return true;
                }

                return $this->isNotDisabled($matches['feature']);
            },
            \ARRAY_FILTER_USE_BOTH
        );
    }

    /**
     * @return array<Feature>
     */
    public function getAll(): array
    {
        return array_values($this->allCommercialFeatures);
    }

    /**
     * Get all the `Feature` instances permitted by the given license
     *
     * @return array<Feature>
     */
    public function getAllAvailable(UnencryptedToken $license): array
    {
        /** @var array<string, string|int|boolean> $toggles */
        $toggles = $license->claims()->get('license-toggles') ?? [];

        $regex = self::REGEX_FEATURE_NAME;

        $allAvailableFeatures = [];
        foreach ($toggles as $toggle => $value) {
            if (!preg_match($regex, $toggle, $matches)) {
                // if it's not a feature name we recognise, skip
                continue;
            }

            if ($value === true && \array_key_exists($matches['feature'], $this->allCommercialFeatures)) {
                $feature = $this->allCommercialFeatures[$matches['feature']];

                $allAvailableFeatures[] = $feature;
            }
        }

        return array_values(array_unique($allAvailableFeatures, \SORT_REGULAR));
    }

    /**
     * @return array<string>
     */
    private function getDisabled(): array
    {
        /** @var array<string> $disabledFeatures */
        $disabledFeatures = $this->systemConfig->get(self::CONFIG_STORE_DISABLED_FEATURES) ?? [];

        return $disabledFeatures;
    }

    /**
     * @param array<string> $features
     */
    private function validateFeatures(array $features): void
    {
        $nonExistingFeatures = array_diff($features, array_keys($this->allCommercialFeatures));

        if (\count($nonExistingFeatures)) {
            throw InvalidFeaturesException::fromFeatures($features);
        }
    }
}
