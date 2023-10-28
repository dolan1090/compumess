// / <reference types="Cypress" />

describe('@storefront @regressions  - Account area regression tests', () => {
    beforeEach(() => {
        cy.authenticate().then(() => {
            return  cy.createCustomerFixtureStorefront()
        }).then(() => {
            cy.visit('/');
        })
    });

    it('should not break the account overview with no orders', () => {
        // Login
        cy.get('#accountWidget')
            .should('be.visible')
            .click();
        cy.get('[href="/account"]')
            .should('be.visible')
            .click();
        cy.get('#loginMail').typeAndCheckStorefront('test@example.com');
        cy.get('#loginPassword').typeAndCheckStorefront('shopware');
        cy.get('.login-submit [type="submit"]').click();

        // Check for basic information
        cy.contains('.account-overview-profile p', 'Mr. Pep Eroni');
        cy.contains('.account-overview-profile p ~ p', 'test@example.com');

        // No latest order yet
        cy.get('.account-overview-card account-overview-newest-order').should('not.exist');
    });
});
