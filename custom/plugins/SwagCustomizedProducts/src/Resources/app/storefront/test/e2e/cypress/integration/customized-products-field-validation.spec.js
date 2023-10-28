// / <reference types="Cypress" />

let product = null;

describe('@storefront - Field Validation', () => {
    beforeEach(() => {
        cy.authenticate().then(() => {
            return cy.createDefaultFixture('category');
        }).then(() => {
            // Read the content of the product.json
            return cy.fixture('product');
        }).then((fixtureProduct) => {
            product = fixtureProduct;
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
                if (!Object.prototype.hasOwnProperty.call(value, 'values')) {
                    return value;
                }
                value.values = value.values.map((item) => {
                    item.taxId = tax.id;
                    return item;
                });

                return value;
            });
        }).then(() => {
            // Create the product
            return cy.createProductFixture(product);
        }).then(() => {
            // Create a default customer
            return cy.createCustomerFixtureStorefront();
        }).then(() => {
            // ...last but not least, visit the storefront
            cy.visit('/');
        });
    });

    it('should show the correct error for input fields', () => {
        // Search for the created product in the storefront
        cy.get('.header-search-input')
            .should('be.visible')
            .type(product.name);
        cy.contains('.search-suggest-product-name', product.name).click();

        cy.get('.swag-customized-products__type-textfield input')
            .should('be.visible');

        cy.contains('.swag-customized-products__type-textfield input + .customized-products-error-subtitle', 'This field is required');

        cy.get('.swag-customized-products__type-textfield input').type('test1234{enter}');

        cy.get('.swag-customized-products__type-textfield input + .customized-products-error-subtitle').should('not.be.visible');
    });

    it('should show the correct error for number fields', () => {
        // Search for the created product in the storefront
        cy.get('.header-search-input')
            .should('be.visible')
            .type(product.name);
        cy.contains('.search-suggest-product-name', product.name).click();

        cy.get('.swag-customized-products__type-numberfield input')
            .should('be.visible');

        cy.contains('.swag-customized-products__type-textfield input + .customized-products-error-subtitle', 'This field is required');

        cy.get('.swag-customized-products__type-numberfield input').type('12{enter}');

        cy.get('.swag-customized-products__type-numberfield input + .customized-products-error-subtitle').should('not.be.visible');
    });

    it('should show the correct error for number textarea', () => {
        // Search for the created product in the storefront
        cy.get('.header-search-input')
            .should('be.visible')
            .type(product.name);
        cy.contains('.search-suggest-product-name', product.name).click();

        cy.get('.swag-customized-products__type-textarea textarea')
            .should('be.visible');

        cy.contains('.swag-customized-products__type-textarea textarea + .customized-products-error-subtitle', 'This field is required');

        cy.get('.swag-customized-products__type-textarea textarea').type('hallo test').blur();

        cy.get('.swag-customized-products__type-textarea textarea + .customized-products-error-subtitle').should('not.be.visible');
    })
});
