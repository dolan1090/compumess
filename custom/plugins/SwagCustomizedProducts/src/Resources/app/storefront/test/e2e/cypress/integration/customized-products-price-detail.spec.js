// / <reference types="Cypress" />

let product;
describe('@storefront - Customized product detail page price box language', () => {
    beforeEach(() => {
        let salesChannelId = '';
        let languageId = '';
        let currencyId = '';
        let deDESnippetSetId = '';

        cy.authenticate().then(() => {
            return cy.searchViaAdminApi({
                endpoint: 'sales-channel',
                data: {
                    field: 'name',
                    value: 'Storefront'
                }
            })
        }).then((salesChannel) => {
            salesChannelId = salesChannel.id;

            return cy.searchViaAdminApi({
                endpoint: 'language',
                data: {
                    field: 'locale.code',
                    value: 'de-DE'
                }
            });
        }).then((language) => {
            languageId = language.id;

            return cy.searchViaAdminApi({
                endpoint: 'currency',
                data: {
                    field: 'isoCode',
                    value: 'EUR'
                }
            });
        }).then((currency) => {
            currencyId = currency.id;

            return cy.searchViaAdminApi({
                endpoint: 'snippet-set',
                data: {
                    field: 'iso',
                    value: 'de-DE'
                }
            });
        }).then((snippetSet) => {
            deDESnippetSetId = snippetSet.id;

            return cy.createViaAdminApi({
                endpoint: 'sales-channel-domain',
                data: {
                    url: `${Cypress.config().baseUrl}/de`,
                    salesChannelId: salesChannelId,
                    languageId: languageId,
                    currencyId: currencyId,
                    snippetSetId: deDESnippetSetId
                }
            });
        }).then(() => {
            return cy.createDefaultFixture(
                'category',
                {
                    translations: {
                        [languageId]: {
                            name: 'Mir doch egal'
                        }
                    }
                },
                'price-detail-category'
            );
        }).then(() => {
            // Read the content of the price-detail-product.json
            return cy.fixture('price-detail-product');
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

                return value;
            });

            // Create the product
            return cy.createCustomProductFixture(product, 'price-detail-product', 'Price detail catalogue');
        }).then(() => {
            // Create a default customer
            return cy.createCustomerFixtureStorefront();
        }).then(() => {

            // Visit the german product detail page
            cy.visit(`/de/detail/${product.id}`);
        });
    });

    it('should translate the price detail box to german', () => {
        // Verify we are on the correct product detail page, by checking the product name
        cy.get('.product-detail-name')
            .should('be.visible')
            .contains(product.name);

        // Check for the price box
        cy.get('.swag-customized-product__price-display').should('not.exist');
        cy.get('.swag-customized-product__price-display').should('be.exist');

        // Check the price box card titel
        cy.contains('.swag-customized-product__price-display > .card-body > .card-title', 'Pro-Stück-Aufschläge');

        // Check the product price label
        cy.contains('.price-display__product-price > .price-display__label', 'Produktpreis');

        // Check the summary label
        cy.contains('.price-display__summary > .price-display__label', 'Zwischensumme');

        // Check the total price label
        cy.contains('.price-display__total-price > .price-display__label', 'Gesamtpreis');
    });
});
