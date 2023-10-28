// / <reference types="Cypress" />

let product;
let propertyGroupName = 'Custom Products colors';
let addedPropertyName = 'Red';

describe('@storefront - Display variant information', () => {
    beforeEach(() => {
        cy.authenticate().then(() => {
            return cy.createDefaultFixture('category')
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
            return cy.createPropertyFixture({
                name: propertyGroupName
            });
        }).then(() => {
            return cy.searchViaAdminApi({
                endpoint: 'property-group',
                data: {
                    field: 'name',
                    value: propertyGroupName
                }
            });
        }).then((propertyGroup) => {
            product.options = [{
                groupId: propertyGroup.id,
                name: addedPropertyName
            }];

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

    it('should open up the customized product in the storefront, still with variant information', () => {
        // Search for the created product in the storefront
        cy.get('.header-search-input')
            .should('be.visible')
            .type(product.name);
        cy.contains('.search-suggest-product-name', product.name).click();

        // Check for the price box
        cy.get('.swag-customized-product__price-display').should('not.exist');
        cy.get('.swag-customized-product__price-display').should('be.exist');

        // Select field (required)
        cy.contains('.swag-customized-products-option__title', 'Example select').should('be.visible');
        cy.contains('.swag-customized-products-option-type-select-checkboxes-label__property', 'Example #1')
            .should('be.visible')
            .click({ force: true });

        // Textfield (required)
        cy.contains('.swag-customized-products-option__title', 'Example textfield')
            .should('be.visible');
        cy.get('.swag-customized-products__type-textfield input')
            .should('be.visible')
            .type('Hello Customized Products Textfield{enter}');

        // Textarea (required);
        cy.get('.swag-customized-products__type-textarea textarea').should('be.visible');
        cy.get('.swag-customized-products__type-textarea textarea')
            .should('be.visible')
            .type('Hello Customized Products Textarea')
            .blur();

        // Numberfield (required)
        cy.contains('.swag-customized-products-option__title', 'Example numberfield').should('be.visible');
        cy.get('.swag-customized-products__type-numberfield input')
            .should('be.visible')
            .type('42');
        cy.contains('.swag-customized-products-option__title', 'Example numberfield').click();

        // Add to cart
        cy.get('.product-detail-buy .btn-buy').click();

        // Off canvas cart
        cy.get('.offcanvas.show').should('be.visible');
        cy.get('.offcanvas .loader').should('not.exist');
        cy.get('.line-item-label').contains(product.name);

        // Check if the variation specifications are still available in the cart
        cy.get('.line-item-details-characteristics').contains(`${propertyGroupName}: ${addedPropertyName}`);

        // Check the configuration
        cy.get('.swag-customized-products__line-item-options-control-wrapper').first().click();
        cy.get('.swag-customized-products__line-item-option-elements').should('be.visible');
        cy.contains('.swag-customized-products__line-item-option-element', 'Example #1');

        cy.get('.offcanvas-cart-actions .btn-primary').click();

        // Login
        cy.get('.checkout-main').should('be.visible');
        cy.get('.login-collapse-toggle').click();
        cy.get('.login-card').should('be.visible');
        cy.get('#loginMail').typeAndCheckStorefront('test@example.com');
        cy.get('#loginPassword').typeAndCheckStorefront('shopware');
        cy.get('.login-submit [type="submit"]').click();

        // Confirm
        cy.get('.confirm-tos .card-title').contains('Terms and conditions and cancellation policy');
        cy.get('.confirm-tos label').scrollIntoView();
        cy.get('.confirm-tos label').click(1, 1);
        cy.get('.confirm-address').contains('Pep Eroni');

        // Finish checkout
        cy.get('#confirmFormSubmit').scrollIntoView();
        cy.get('#confirmFormSubmit').click();
        cy.get('.finish-header').contains('Thank you for your order with Demostore!');

        // Let's check the calculation on /finish as well
        cy.contains(product.name);

        // Switch to account area
        /* TODO@CUS-187 - Re-enable the test
        cy.visit('/account/');

        // Show order in overview
        cy.get('.order-table .order-item-actions > .order-hide-btn')
            .should('be.visible')
            .click();
        cy.get('.order-item-variants-properties').contains(`${propertyGroupName}: ${addedPropertyName}`);

        // Navigate to orders
        cy.get('main a.account-aside-item[title="Orders"]')
            .should('be.visible')
            .click();

        // Show order in orders
        cy.get('.order-table:nth-child(1) .order-item-actions > .order-hide-btn')
            .should('be.visible')
            .click();

        // Open configuration
        cy.get('.order-table:nth-child(1) .swag-customized-products-cart__title-toggle').click();
        cy.get('.order-item-variants-properties').contains(`${propertyGroupName}: ${addedPropertyName}`);
        */
    });
});
