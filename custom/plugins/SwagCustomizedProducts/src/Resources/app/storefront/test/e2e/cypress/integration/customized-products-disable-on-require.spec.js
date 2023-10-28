// / <reference types="Cypress" />

let product;

describe('@storefront - Check for disabled add to cart / buy button with required fields', () => {
    beforeEach(() => {
        cy.authenticate().then(() => {
            return cy.createDefaultFixture('category');
        }).then(() => {
            // Read the content of the product.json
            return cy.fixture('product');
        }).then((fixtureProduct) => {
            product = fixtureProduct;

            // update the fixture with specific details for this test suite
            product.swagCustomizedProductsTemplate.options = product.swagCustomizedProductsTemplate.options.map((option) => {
                if (option.type !== 'checkbox') {
                    option.required = true; // set every option to required for this test suite (where possible)
                }

                if (option.type === 'select' || option.type === 'colorselect') {
                    // no default values in select options
                    option.values = option.values.map((value) => {
                        value.default = false;
                        return value;
                    });
                }

                return option;
            });

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

    it('should disable the checkout / buy button', () => {
        // Search for the created product in the storefront
        cy.get('.header-search-input')
            .should('be.visible')
            .type(product.name);
        cy.contains('.search-suggest-product-name', product.name).click();

        // checkout / buy button should be disabled
        cy.get('.btn-buy').should('be.disabled');

        // fill in required option and still expect the button to be disabled (because of more required options)
        cy.get('.swag-customized-products__type-textfield input').type('test1234{enter}');
        cy.get('.btn-buy').should('be.disabled');

        // fill in required option and still expect the button to be disabled (because of more required options)
        cy.get('.swag-customized-products__type-textarea textarea').type('test1234{enter}');
        cy.get('.btn-buy').should('be.disabled');

        // fill in required option and still expect the button to be disabled (because of more required options)
        cy.get('.swag-customized-products__type-numberfield input').type('42{enter}');
        cy.get('.btn-buy').should('be.disabled');

        // fill in required option and still expect the button to be disabled (because of more required options)
        // Datefield
        cy.get('.swag-customized-products__type-datetime > .input-group > input[type="text"].swag-customized-products-options-datetime')
            .should('be.visible')
            .click();
        cy.get('.flatpickr-calendar').should('be.visible');
        cy.get('.flatpickr-day.today').click();
        cy.get('.btn-buy').should('be.disabled');

        // fill in required option and still expect the button to be disabled (because of more required options)
        // Time field
        cy.get('.swag-customized-products__type-timestamp > .input-group > input[type="text"].swag-customized-products-options-datetime')
            .should('be.visible')
            .click();
        cy.get('.flatpickr-calendar').should('be.visible');
        cy.get('.numInputWrapper .flatpickr-hour').type('3');
        cy.get('.btn-buy').should('be.disabled');

        // fill in required option and still expect the button to be disabled (because of more required options)
        cy.contains('Example Purple').click();

        // fill in required option and still expect the button to be disabled (because of more required options)
        cy.get('.swag-customized-products__type-select .swag-customized-products__boolean:nth(0)').click();

        // add to cart / buy button should now be enabled (all required options are filled)
        cy.get('.loader').should('not.exist');

        cy.get('.btn-buy')
            .should('be.enabled')
            .click();

        cy.get('.offcanvas-cart')
            .should('be.visible')
            .should('contain', product.name);
    });
});
