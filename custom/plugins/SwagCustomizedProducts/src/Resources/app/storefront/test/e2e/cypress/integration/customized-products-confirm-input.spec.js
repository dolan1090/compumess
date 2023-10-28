// / <reference types="Cypress" />

let product;
describe('@storefront - default mode with confirm input option enabled', () => {
    beforeEach(() => {
        cy.authenticate().then(() => {
            return cy.createDefaultFixture('category', {}, 'confirm-input-category')
        }).then(() => {            // Read the content of the confirm-input-product.json
            return cy.fixture('confirm-input-product');
        }).then((fixtureProduct) => {
            product = fixtureProduct;
            product.swagCustomizedProductsTemplate.confirmInput = true;
            // Now fetch the tax based on name
            return cy.searchViaAdminApi({
                endpoint: 'tax',
                data: {
                    field: 'name',
                    value: 'Standard rate'
                }
            });
        }).then((tax) => {
           // Add the tax id to the options and option values
            product.swagCustomizedProductsTemplate.options = product.swagCustomizedProductsTemplate.options.map((value) => {
                value.taxId = tax.id;

                return value;
            });

            // Create the product
            return cy.createCustomProductFixture(product, 'confirm-input-product');
        }).then(() => {
            // Create a default customer
            return cy.createCustomerFixtureStorefront();
        }).then(() => {
            // Visit the product detail page
            cy.visit(`/detail/${product.id}`);
        });
    });

    it('should check if the input can be confirmed', () => {
        // Search for the created product in the storefront
        cy.get('.product-detail-name')
            .should('be.visible')
            .contains(product.name);

        // Check buy button is disabled because we did not confirm our input
        cy.get('.product-detail-buy .btn-buy').should('be.disabled');

        // Checking the confirm input should enable the buy button
        cy.get('.swag-customized-products-confirm-input-container label').click();
        cy.get('.product-detail-buy .btn-buy').should('not.disabled').click();

        // Off canvas cart
        cy.get('.offcanvas.show').should('be.visible');
        cy.get('.offcanvas .loader').should('not.exist');
        cy.get('.line-item-label').contains(product.name);
    });
});
