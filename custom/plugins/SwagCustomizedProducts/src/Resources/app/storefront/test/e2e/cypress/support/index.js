// Require test suite commons
require('@shopware-ag/e2e-testsuite-platform/cypress/support');

// Custom storefront commands
require('./commands/commands');

beforeEach(() => {
    return cy.authenticate().then(() => {
        if (!Cypress.env('SKIP_INIT')) {
            return cy.setToInitialState().then(() => {
                return cy.authenticate();
            });
        }
    });
});
