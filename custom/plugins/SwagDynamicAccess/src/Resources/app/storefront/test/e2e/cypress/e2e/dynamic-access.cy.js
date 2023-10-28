// / <reference types="Cypress" />

describe('@storefront: Test dynamic access', () => {
    beforeEach(() => {
        cy.authenticate().then(() => {
            cy.setToInitialState()
                .then(() => {
                    return cy.createCustomerFixtureStorefront();
                });
        });
    });

    it('should hide product based on rule', () => {
        cy.createProductFixture();

        cy.intercept({
            url: '/api/_action/sync',
            method: 'POST',
        }).as('saveProduct');

        cy.visit(`${Cypress.env('admin')}#/sw/product/index`);

        cy.get('.sw-search-bar__input').typeAndCheckSearchField('Product name');

        cy.get('.sw-data-grid__cell--name > .sw-data-grid__cell-content').click();

        cy.get('.sw-loader').should('not.exist');

        cy.get('.sw-product-category-form__customer-restrictions-title')
            .scrollIntoView()
            .contains('Dynamic Access');

        cy.get('.sw-product-category-form__customer-restrictions-field-select')
            .typeMultiSelectAndCheck('Customers from USA');

        cy.get('.sw-product-detail__save-action').click();

        cy.wait('@saveProduct')
            .its('response.statusCode').should('eq', 200)

        cy.visit('/');

        cy.get('.alert-content')
            .contains('No products found.');
    });

    it('should hide category based on rule', () => {
        cy.intercept({
            url: '/api/category/*',
            method: 'PATCH',
        }).as('saveCategory');

        let categoryId;

        cy.searchViaAdminApi({
            endpoint: 'cms-page',
            data: {
                field: 'name',
                type: 'equals',
                value: 'Default category layout'
            }
        }).then(res => {
            const cmsPageId = res.id;

            return cy.createCategoryFixture({ cmsPageId });
        }).then(res => {
            categoryId = res.id;

            return cy.searchViaAdminApi({
                endpoint: 'category',
                data: {
                    field: 'name',
                    type: 'equals',
                    value: 'Home'
                }
            });
        }).then(res => {
            const parentId = res.id;

            cy.updateViaAdminApi('category', categoryId, {
                data: {
                    parentId
                }
            });
        });

        // check existence of sub-category
        cy.visit('/');

        cy.get('.main-navigation-menu')
            .children()
            .should('have.length', 2);

        cy.get('.main-navigation-menu :last-child')
            .contains('Subcategory #1');

        cy.searchViaAdminApi({
            endpoint: 'category',
            data: {
                field: 'name',
                type: 'equals',
                value: 'Subcategory #1'
            }
        }).then((res) => {
            const categoryId = res.id;

            cy.visit(`${Cypress.env('admin')}#/sw/category/index/${categoryId}`);
        });

        cy.get('.sw-loader').should('not.exist');

        cy.get('.sw-category-detail-base__customer-restrictions-field')
            .typeMultiSelectAndCheck('Customers from USA');

        cy.get('.sw-category-detail__save-action')
            .click();

        cy.wait('@saveCategory')
            .its('response.statusCode').should('eq', 204);

        cy.visit('/');

        cy.get('.main-navigation-menu')
            .children()
            .should('have.length', 1);

        cy.get('.main-navigation-menu a')
            .contains('Home');
    });

    it('should hide landing pages based on rule', () => {
        let salesChannelId;

        cy.intercept({
            url: '/api/landing-page/*',
            method: 'PATCH'
        }).as('saveLandingPage');

        cy.searchViaAdminApi({
            endpoint: 'sales-channel',
            data: {
                field: 'name',
                type: 'equals',
                value: 'Storefront'
            }
        }).then(res => {
            salesChannelId = res.id;

            return cy.searchViaAdminApi({
                endpoint: 'cms-page',
                data: {
                    field: 'name',
                    type: 'equals',
                    value: 'Imprint'
                }
            });
        }).then(res => {
            cy.createDefaultFixture('landing-page', {
                cmsPageId: res.id,
                salesChannels: [
                    {
                        id: salesChannelId
                    }
                ]
            });
        });

        cy.visit('/landingpage');

        cy.get('.cms-section h2')
            .contains('Imprint');

        cy.searchViaAdminApi({
            endpoint: 'landing-page',
            data: {
                field: 'name',
                type: 'equals',
                value: 'Testingpage'
            }
        }).then((res) => {
            const landingPageId = res.id;

            cy.visit(`${Cypress.env('admin')}#/sw/category/landingPage/${landingPageId}`);
        });

        cy.get('.sw-landing-page-detail-base__customer-restrictions')
            .scrollIntoView();

        cy.get('.sw-landing-page-detail-base__customer-restrictions .sw-select-rule-create')
            .typeMultiSelectAndCheck('Customers from USA');

        cy.get('.sw-category-detail__save-landing-page-action')
            .click();

        cy.wait('@saveLandingPage')
            .its('response.statusCode').should('eq', 204);

        // checking if user is not able to visit landing page anymore by making a request
        cy.request({
            url: '/landingpage',
            failOnStatusCode: false
        }).then(xhr => {
            expect(xhr).to.have.property('status', 404);
        });
    });
});
