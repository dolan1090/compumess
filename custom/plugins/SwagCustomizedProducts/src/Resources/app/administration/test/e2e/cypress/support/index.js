// Require test suite commons
require('@shopware-ag/e2e-testsuite-platform/cypress/support');

/**
 * Manually triggers the exclusion tree generation
 *
 * @function
 * @memberOf Cypress.Chainable#
 * @name generateExclusionTree
 * @param {String} [templateId] - Custom Products Template Id, needed to know which exclusion true should be generated
 */
Cypress.Commands.add('generateExclusionTree', (templateId) => {
    return cy.requestAdminApi('POST', `${Cypress.env('apiPath')}/_action/swag-customized-products-template/${templateId}/tree`);
});

beforeEach(() => {
    return cy.authenticate().then(() => {
        if (!Cypress.env('SKIP_INIT')) {
            return cy.setToInitialState().then(() => {
                return cy.authenticate();
            });
        }
    });
});
