interface SpecificFeature {
    code: string,
    name: string,
    description: string,
    enabled: boolean,
    type: string,
}

interface CustomerSpecificFeaturesEntity {
    features: SpecificFeature[],
}

export type {
    SpecificFeature,
    CustomerSpecificFeaturesEntity,
}
