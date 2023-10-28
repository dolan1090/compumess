// / <reference types="Cypress" />

let product;

describe('@storefront - Default and step-by-step mode', () => {
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
            product.swagCustomizedProductsTemplate.stepByStep = true;
            return cy.createProductFixture(product);
        }).then(() => {
            // Create a default customer
            return cy.createCustomerFixtureStorefront();
        }).then(() => {
            // ...last but not least, visit the storefront
            cy.visit('/');
        });
    });

    it('should open up the customized product in the storefront with step-by-step mode', () => {
        // Search for the created product in the storefront
        cy.get('.header-search-input')
            .should('be.visible')
            .type(product.name);
        cy.contains('.search-suggest-product-name', product.name).click();

        // Check for the price box
        cy.get('.swag-customized-product__price-display').should('not.exist');
        cy.get('.swag-customized-product__price-display').should('be.exist');

        // Check for the product price
        cy.contains('.price-display__product-price > .price-display__label', 'Product price');
        cy.contains('.price-display__product-price > .price-display__price', '€10.00*');

        // Check the total price
        cy.contains('.price-display__total-price > .price-display__price', '€50.00*');

        // Step-by-Step mode should be active and is started with the click
        cy.contains('.swag-customized-products__title', product.swagCustomizedProductsTemplate.displayName);
        cy.contains('.swag-customized-products-start-wizard', 'Configure product').click();

        // click next until third option
        cy.contains('.swag-customized-products-navigation__text', product.swagCustomizedProductsTemplate.options[0].displayName);
        cy.get('.swag-customized-products-pager__button.btn-next').should('be.visible').click();
        cy.contains('.swag-customized-products-navigation__text', product.swagCustomizedProductsTemplate.options[1].displayName);
        cy.get('.swag-customized-products-pager__button.btn-next').should('be.visible').click();

        // reload page, starting point should be shown again
        cy.reload();
        cy.contains('.swag-customized-products__title', product.swagCustomizedProductsTemplate.displayName);
        cy.contains('.swag-customized-products-start-wizard', 'Configure product');
    });
});
