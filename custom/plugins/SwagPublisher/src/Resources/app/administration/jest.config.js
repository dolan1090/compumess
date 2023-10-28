module.exports = {
    reporters: [
        'default', [
            'jest-junit',
            {
                'suiteName': 'Administration Unit',
                'outputDirectory': '../../../../build/artifacts/administration-unit',
                'outputName': 'administration-unit.xml',
                'uniqueOutputName': 'false'
            }
        ]
    ],
    errorOnDeprecated: true,
    displayName: {
        name: 'Administration',
        color: 'lime'
    },
    collectCoverage: true,
    collectCoverageFrom: ['src/**/*.js'],
    coverageDirectory: '../../../../build/artifacts/administration-unit',
    timers: 'fake'
};
