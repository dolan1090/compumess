// / <reference types="Cypress" />
const uuid = require('uuid/v4');

const selector = {
    input: {
        internalName: '#sw-field--template-internalName',
        displayName: '#sw-field--template-displayName',
        active: 'input[name=sw-field--template-active]',
        description: '.sw-text-editor__content',
        optionName: '#sw-field--newOption-displayName',
        optionType: '#sw-field--newOption-type',
        numberfield: {
            min: '#sw-field--option-typeProperties-minValue',
            max: '#sw-field--option-typeProperties-maxValue',
            step: '#sw-field--option-typeProperties-interval',
            defaultValue: '#sw-field--option-typeProperties-defaultValue'
        },
        date: {
            minDate: '#sw-field--option-typeProperties-minDate ~ .form-control.input'
        },
        time: {
            startTime: '#sw-field--option-typeProperties-startTime ~ .form-control.input'
        },
        conditionTreeSelect: '.sw-condition-tree-node .sw-entity-single-select__selection-input',
        selectResult: '.sw-select-result'
    },
    button: {
        addTemplate: '[href="#/swag/customized/products/create"]',
        saveTemplate: '.swag-customized-products-detail__save-action',
        addOption: '.sw-modal__footer button.sw-button--primary',
        applyOption: '.swag-customized-products-option-detail-modal .sw-modal__footer button.sw-button--primary',
        cancelOption: '.swag-customized-products-option-detail-modal .btn-cancel'
    },
    optionGrid: {
        skeleton: '.sw-data-grid-skeleton',
        nameCell: '.sw-data-grid__cell--displayName'
    },
    emptyState: '.swag-customized-products-detail-base__empty-state',
    loader: '.sw-loader',
    detailModal: '.swag-customized-products-option-detail-modal'
};

describe('Administration: Custom products', () => {
    const productId = uuid().replace(/-/g, '');
    const productName = "CustomTasse" + uuid().replace(/-/g, '');

    beforeEach(() => {
        // Clean previous state and prepare Administration
        cy.authenticate().then(() => {
            cy.setLocaleToEnGb();
        }).then(() => {
            cy.createProductFixture({
                id: productId,
                name: productName,
                productNumber: uuid().replace(/-/g, '')
            });
        }).then(() => {
            cy.openInitialPage(`${Cypress.env('admin')}/#/swag/customized/products/index`);

            cy.get('.sw-skeleton').should('not.exist');
            cy.get('.sw-loader').should('not.exist');
        });
    });

    it('@package @general: can navigate to custom products module', () => {
        cy.get('.swag-customized-products-list').should('exist');
    });

    it('@package @general: can add a new template', () => {
        cy.get(selector.button.addTemplate).click();
        cy.get(selector.input.internalName).type('lorem-ipsum');
        cy.get(selector.input.displayName).type('Lorem ipsum');
        cy.get(selector.input.description).type('Lorem ipsum dolor sit amet...');
        cy.get(selector.button.saveTemplate).click();

        cy.get(selector.emptyState).should('exist');
    });

    it('@package @general: can add a new template and option', () => {
        cy.get(selector.button.addTemplate).click();
        cy.get(selector.input.internalName).type('lorem-ipsum');
        cy.get(selector.input.displayName).type('Lorem ipsum');
        cy.get(selector.input.description).type('Lorem ipsum dolor sit amet...');
        cy.get(selector.button.saveTemplate).click();

        cy.get(selector.emptyState).should('not.exist');

        // Add checkbox
        cy.contains('Add option').click();
        cy.get(selector.input.optionName).type('Check this');
        cy.get(selector.input.optionType).select('Checkbox');

        cy.get(selector.button.addOption).click();
        cy.get(selector.button.applyOption).click();


        // Add an additional numberfield
        cy.contains('Add option').click();
        cy.get(selector.input.optionName).type('Number this');
        cy.get(selector.input.optionType).select('Number field');
        cy.get(selector.button.addOption).click();

        // Fill in typeProperties
        cy.get(selector.input.numberfield.min).clear().type('2');
        cy.get(selector.input.numberfield.max).clear().type('22');
        cy.get(selector.input.numberfield.step).clear().type('2');
        cy.get(selector.input.numberfield.defaultValue).clear().type('12');
        cy.get(selector.button.applyOption).click();

        // safety check if modal is closed
        cy.get(selector.input.numberfield.min).should('not.exist');

        // Reopen to check values
        cy.get('.sw-data-grid__cell-content').contains('Number this').click();
        cy.get(selector.input.numberfield.min).should('have.value', '2');
        cy.get(selector.input.numberfield.max).should('have.value', '22');
        cy.get(selector.input.numberfield.step).should('have.value', '2');
        cy.get(selector.input.numberfield.defaultValue).should('have.value', '12');
        cy.get(selector.button.cancelOption).click();
    });

    it('@package @general: can add a new template with options and exclusions', () => {
        cy.intercept({
            url: `${Cypress.env('apiPath')}/_action/version/merge/swag-customized-products-template/**`,
            method: 'post'
        }).as('saveData');

        cy.get(selector.button.addTemplate).click();
        cy.get(selector.input.internalName).type('lorem-ipsum');
        cy.get(selector.input.displayName).type('Lorem ipsum');
        cy.get(selector.button.saveTemplate).click();

        cy.get(selector.emptyState).should('not.exist');

        // Add checkbox
        cy.contains('Add option').click();
        cy.get(selector.input.optionName).type('Check this');
        cy.get(selector.input.optionType).select('Checkbox');
        cy.get(selector.button.addOption).click();
        cy.get(selector.button.applyOption).click();

        // Add an additional numberfield
        cy.contains('Add option').click();
        cy.get(selector.input.optionName).type('Number #1');
        cy.get(selector.input.optionType).select('Number field');
        cy.get(selector.button.addOption).click();
        cy.get(selector.button.applyOption).click();

        // Add exclusion
        cy.contains('.sw-card__title', 'Excluded combinations').scrollIntoView();
        cy.contains('Add excluded combination').click();
        cy.get('#sw-field--exclusion-name').type('Hammer-Exclusion');
        cy.get('.sw-condition-tree-node .sw-entity-single-select__selection-input').first().type('Check this').type('{enter}');
        cy.get('.sw-condition-tree-node .sw-entity-single-select__selection-input').eq(1).type('Checked').type('{enter}');

        cy.get('.sw-condition-tree-node .sw-entity-single-select__selection-input').eq(2).type('Number #1').type('{enter}');
        cy.get('.sw-condition-tree-node .sw-entity-single-select__selection-input').eq(3).type('Standard value').type('{enter}');
        cy.get('.sw-modal__dialog .sw-button--primary').click();

        // Check exclusion on template detail page
        cy.get('.swag_customized_products_exclusion_list .sw-data-grid__row--0 .sw-data-grid__cell-content').eq(1).contains('Hammer-Exclusion');
        cy.get('.swag-customized-products-detail__save-action').click();

        cy.wait('@saveData').its('response.statusCode').should('equal', 204);
    });

    it('@package @general: can create and edit a template and with date/time options', () => {
        const dateFieldDisplayName = 'That is, indeed, a datefield.';
        const timeFieldDisplayName = 'Stop! Hammertime!;'

        cy.get(selector.button.addTemplate).click();
        cy.get(selector.input.internalName).type('lorem-ipsum');
        cy.get(selector.input.displayName).type('Lorem ipsum');
        cy.get(selector.input.description).type('Lorem ipsum dolor sit amet...');
        cy.get(selector.button.saveTemplate).click();

        cy.get(selector.emptyState).should('not.exist');

        // Add datetime with no initial configuration
        cy.contains('Add option').click();
        cy.get(selector.input.optionName).type(dateFieldDisplayName);
        cy.get(selector.input.optionType).select('Date field');

        cy.get(selector.button.addOption).click();
        cy.get(selector.button.applyOption).click();

        // Save the template
        cy.get(selector.button.saveTemplate).click();
        cy.get(selector.emptyState).should('not.exist');

        // Reopen and edit
        cy.contains(selector.optionGrid.nameCell, dateFieldDisplayName).click();
        cy.get(selector.input.date.minDate).type('2222-09-01{enter}');
        cy.get(selector.button.applyOption).click();

        // Open again and check
        cy.get(selector.detailModal).should('not.exist');
        cy.get(selector.optionGrid.skeleton).should('not.exist');
        cy.contains(selector.optionGrid.nameCell, dateFieldDisplayName).click();
        cy.get(selector.input.date.minDate).should('have.value', '2222-09-01');
        cy.get(selector.button.cancelOption).click();

        // Add an additional timefield
        cy.contains('Add option').click();
        cy.get(selector.input.optionName).type(timeFieldDisplayName);
        cy.get(selector.input.optionType).select('Time field');
        cy.get(selector.button.addOption).click();

        // Fill in initial configuration
        cy.get(selector.input.time.startTime).clear().type('12:34{enter}');
        cy.get(selector.button.applyOption).click();

        // Save the template
        cy.get(selector.button.saveTemplate).click();
        cy.get(selector.emptyState).should('not.exist');
        cy.get(selector.loader).should('not.exist');
        cy.get(selector.optionGrid.skeleton).should('not.exist');

        // Reopen to check values (date field)
        cy.contains(selector.optionGrid.nameCell, dateFieldDisplayName).scrollIntoView();
        cy.contains(selector.optionGrid.nameCell, dateFieldDisplayName)
            .should('be.visible')
            .click();
        cy.get(selector.input.date.minDate).should('have.value', '2222-09-01');
        cy.get(selector.button.cancelOption).click();

        // safety check if modal is closed
        cy.get(selector.input.date.minDate).should('not.exist');

        // Reopen to check values (time field)
        cy.get(selector.optionGrid.nameCell).contains(timeFieldDisplayName)
            .should('be.visible')
            .click();
        cy.get(selector.input.time.startTime).should('have.value', '12:34');
    });

    it('@package @general: can add a new template and try to add an option with the empty state remaining present', () => {
        cy.get(selector.button.addTemplate).click();
        cy.get(selector.input.internalName).type('lorem-ipsum');
        cy.get(selector.input.displayName).type('Lorem ipsum');
        cy.get(selector.input.description).type('Lorem ipsum dolor sit amet...');
        cy.get(selector.button.saveTemplate).click();

        cy.get(selector.emptyState).should('exist');

        cy.contains('Add option').click();
        cy.get(selector.input.optionName).type('Check this');
        cy.get(selector.input.optionType).select('Checkbox');

        cy.get(selector.button.addOption).click();
        cy.get(selector.button.cancelOption).click();

        cy.get(selector.emptyState).should('exist');
    });

    it('@package @general: can duplicate a template with options and exclusions', () => {
        cy.intercept({
            url: `${Cypress.env('apiPath')}/_action/version/merge/swag-customized-products-template/**`,
            method: 'post'
        }).as('saveData');

        cy.intercept({
            url: `${Cypress.env('apiPath')}/_action/clone/swag-customized-products-template/**`,
            method: 'post'
        }).as('duplicateData');

        cy.intercept({
            url: `${Cypress.env('apiPath')}/search/swag-customized-products-template`,
            method: 'post'
        }).as('searchCustomProduct');

        const displayName = 'Lorem ipsum';

        cy.get(selector.button.addTemplate).click();
        cy.get(selector.input.internalName).type('lorem-ipsum');
        cy.get(selector.input.displayName).type(displayName);
        cy.get(selector.input.active).click();
        cy.get(selector.button.saveTemplate).click();

        cy.get(selector.emptyState).should('not.exist');

        // Add checkbox
        cy.contains('Add option').click();
        cy.get(selector.input.optionName).type('Check this');
        cy.get(selector.input.optionType).select('Checkbox');
        cy.get(selector.button.addOption).click();
        cy.get(selector.button.applyOption).click();

        // Add an additional numberfield
        cy.contains('Add option').click();
        cy.get(selector.input.optionName).type('Number #1');
        cy.get(selector.input.optionType).select('Number field');
        cy.get(selector.button.addOption).click();
        cy.get(selector.button.applyOption).click();

        // Add exclusion
        cy.contains('.sw-card__title', 'Excluded combinations').scrollIntoView();
        cy.contains('Add excluded combination').click();
        cy.get('#sw-field--exclusion-name').type('Hammer-Exclusion');
        cy.get(selector.input.conditionTreeSelect).first()
            .type('Check this')
            .type('{enter}');
        cy.get(selector.input.conditionTreeSelect).eq(1)
            .type('Checked')
            .type('{enter}');

        cy.get(selector.input.conditionTreeSelect).eq(2)
            .type('Number #1')
            .type('{enter}');
        cy.get(selector.input.conditionTreeSelect).eq(3)
            .type('Not standard value')
            .type('{enter}');
        cy.get('.sw-modal__dialog .sw-button--primary').click();

        // Check exclusion on template detail page
        cy.get('.swag_customized_products_exclusion_list .sw-data-grid__row--0 .sw-data-grid__cell-content').eq(1)
            .contains('Hammer-Exclusion');
        cy.get('.swag-customized-products-detail__context-menu-save-action').click();
        cy.get('.swag-customized-products-detail__context-menu-duplicate-action').click();
        cy.get(selector.emptyState).should('not.exist');

        cy.wait('@duplicateData').its('response.statusCode').should('equal', 200);
        cy.wait('@searchCustomProduct').its('response.statusCode').should('equal', 200);

        cy.get(selector.input.internalName).should('have.value', 'lorem-ipsum (Copy)');
        cy.get(selector.input.displayName).should('have.value', `${displayName} (Copy)`);
        cy.get(selector.input.active).should('not.be.checked');
        cy.get('.swag_customized_products_exclusion_list .sw-data-grid__row--0 .sw-data-grid__cell-content').eq(1)
            .contains('Hammer-Exclusion');

        // Get back and check if both templates are in list
        cy.get('.smart-bar__back-btn').click();
        cy.get('.sw-data-grid__cell--displayName').should('have.length', 2);
    });

    // TODO: To be fixed in CUS-565
    it.skip('@package @general: can save a template with options and exclusions and validate those in the storefront', () => {
        cy.intercept({
            url: `${Cypress.env('apiPath')}/swag-customized-products-template`,
            method: 'post'
        }).as('createTemplate');

        cy.intercept({
            url: `${Cypress.env('apiPath')}/swag-customized-products-template/**/options`,
            method: 'post'
        }).as('createOption');

        cy.intercept({
            url: `${Cypress.env('apiPath')}/swag-customized-products-template/**/options/**`,
            method: 'patch'
        }).as('updateOption');

        cy.intercept({
            url: `${Cypress.env('apiPath')}/_action/version/merge/swag-customized-products-template/**`,
            method: 'post'
        }).as('saveData');

        cy.intercept({
            url: `${Cypress.env('apiPath')}/search/swag-customized-products-template`,
            method: 'post'
        }).as('searchCustomProduct');

        cy.intercept({
            url: `${Cypress.env('apiPath')}/search/product`,
            method: 'post'
        }).as('searchProduct');

        cy.intercept({
            url: `${Cypress.env('apiPath')}/search/swag-customized-products-template-option`,
            method: 'post'
        }).as('searchOption');

        cy.intercept({
            url: `${Cypress.env('apiPath')}/_action/sync`,
            method: 'post'
        }).as('saveProduct');

        const displayName = 'Lorem ipsum';

        cy.get(selector.button.addTemplate).click();

        cy.get('.sw-skeleton').should('not.exist');
        cy.get('.sw-loader').should('not.exist');

        cy.get(selector.input.internalName).type('lorem-ipsum');
        cy.get(selector.input.displayName).type(displayName);
        cy.get(selector.input.active).click();
        cy.get(selector.button.saveTemplate).click();

        cy.wait('@createTemplate').its('response.statusCode').should('equal', 204);
        cy.wait('@searchCustomProduct').its('response.statusCode').should('equal', 200);

        cy.get('.sw-skeleton').should('not.exist');
        cy.get('.sw-loader').should('not.exist');

        cy.get(selector.input.internalName).should('have.value', 'lorem-ipsum');
        cy.get(selector.input.displayName).should('have.value', displayName);
        cy.get(selector.input.active).should('be.checked');

        // Add checkbox
        cy.get('.swag-customized-products-detail-base__add-option-action').contains('Add option').click();

        cy.get('.sw-modal__dialog').should('be.visible');

        cy.get(selector.input.optionName).type('Check this');
        cy.get(selector.input.optionType).select('Checkbox');
        cy.get(selector.button.addOption).click();

        cy.wait('@createOption').its('response.statusCode').should('equal', 204);
        cy.get('.sw-modal.swag-customized-products-option-detail-modal').should('be.visible');

        cy.get(selector.button.applyOption).click();

        cy.wait('@updateOption').its('response.statusCode').should('equal', 204);
        cy.get('.sw-modal.swag-customized-products-option-detail-modal').should('not.exist');

        // Add an additional numberfield
        cy.get('.swag-customized-products-detail-base__add-option-action').contains('Add option').click();

        cy.get('.sw-modal__dialog').should('be.visible');

        cy.get(selector.input.optionName).type('Number #1');
        cy.get(selector.input.optionType).select('Number field');
        cy.get(selector.button.addOption).click();

        cy.wait('@createOption').its('response.statusCode').should('equal', 204);
        cy.get('.sw-modal.swag-customized-products-option-detail-modal').should('be.visible');

        cy.get(selector.button.applyOption).click();

        cy.wait('@updateOption').its('response.statusCode').should('equal', 204);
        cy.get('.sw-modal.swag-customized-products-option-detail-modal').should('not.exist');

        // Add exclusion
        cy.contains('.sw-card__title', 'Excluded combinations').scrollIntoView();
        cy.contains('Add excluded combination').click();

        cy.get('.sw-modal__dialog').should('be.visible');

        cy.get('#sw-field--exclusion-name').type('Hammer-Exclusion');

        cy.get(selector.input.conditionTreeSelect).eq(0).typeAndCheck('Check this');
        cy.wait('@searchOption').its('response.statusCode').should('equal', 200);
        cy.get('.sw-select-result').should('be.visible');
        cy.contains('.sw-highlight-text', 'Check this').click();

        cy.get(selector.input.conditionTreeSelect).eq(1).typeAndCheck('Checked');
        cy.wait('@searchOption').its('response.statusCode').should('equal', 200);
        cy.get('.sw-select-result').should('be.visible');
        cy.contains('.sw-highlight-text', 'Checked').click();

        cy.get(selector.input.conditionTreeSelect).eq(2).typeAndCheck('Number #1');
        cy.wait('@searchOption').its('response.statusCode').should('equal', 200);
        cy.get('.sw-select-result').should('be.visible');
        cy.contains('.sw-highlight-text', 'Number #1').click();

        cy.get(selector.input.conditionTreeSelect).eq(3).typeAndCheck('Not standard value');
        cy.wait('@searchOption').its('response.statusCode').should('equal', 200);
        cy.get('.sw-select-result').should('be.visible');
        cy.contains('.sw-highlight-text', 'Not standard value').click();

        cy.get('.sw-modal__dialog .sw-button--primary').click();

        // Check exclusion on template detail page
        cy.get('.swag_customized_products_exclusion_list .sw-data-grid__row--0 .sw-data-grid__cell-content').eq(1)
            .contains('Hammer-Exclusion');
        cy.get(selector.button.saveTemplate).click();
        cy.get(selector.emptyState).should('not.exist');

        cy.wait('@saveData').its('response.statusCode').should('equal', 204);
        cy.wait('@searchCustomProduct').its('response.statusCode').should('equal', 200);

        cy.get('.sw-skeleton').should('not.exist');

        cy.get(selector.input.internalName).should('have.value', 'lorem-ipsum');
        cy.get(selector.input.displayName).should('have.value', displayName);
        cy.get(selector.input.active).should('be.checked');
        cy.get('.swag_customized_products_exclusion_list .sw-data-grid__row--0 .sw-data-grid__cell-content').eq(1)
            .contains('Hammer-Exclusion');

        // Get the Id of the current template
        cy.searchViaAdminApi({
            endpoint: 'swag-customized-products-template',
            data: {
                field: 'displayName',
                value: displayName
            }
        }).then((template) => {
            cy.get(selector.emptyState).should('not.exist');

            // Assign template to product
            cy.visit(`${Cypress.env('admin')}#/sw/product/detail/${productId}/specifications`);

            cy.get('.sw-product-detail-specification__measures-packaging').should('be.visible');
            cy.get('.swag-customized-products-product-assignment__select-container .sw-entity-single-select__selection').scrollIntoView();
            cy.get('.swag-customized-products-product-assignment__select-container')
                .typeSingleSelectAndCheck('lorem-ipsum', '.swag-customized-products-product-assignment__select-container');

            cy.get('.swag-customized-products-product-assignment__card-title.sw-card__title').click();

            cy.get('.sw-button-process__content').click();
            cy.wait('@saveProduct').its('response.statusCode').should('equal', 200);

            cy.generateExclusionTree(template.id).then(() => {
                cy.visit(`/detail/${productId}`);

                cy.wait('@searchProduct').its('response.statusCode').should('equal', 200);

                // Insert / Check both options
                cy.get('.swag-customized-products__title-link-container').eq(1).should('be.visible');
                cy.get('.swag-customized-products__title-link-container').eq(0).click();
                cy.get('.swag-customized-products__boolean .form-check-label').should('be.visible');
                cy.get('.swag-customized-products__boolean .form-check-label').click({ force: true });

                cy.get('.swag-customized-products__title-link-container').eq(1).should('be.visible');
                cy.get('.swag-customized-products__title-link-container').eq(1).click();
                cy.get('.swag-customized-products__type-numberfield input').should('be.visible');
                cy.get('.swag-customized-products__type-numberfield input').type('100').blur();

                // Violation list should be visible
                cy.get('.swag-customized-products__violation-list-holder').should('be.visible');
                cy.get('.btn-buy').should('be.disabled');

                // Uncheck first option, violation list should disappear and buy button shouldn't be disabled
                cy.get('.swag-customized-products__boolean .form-check-label').click();
                cy.get('.swag-customized-products__violation-list-holder').should('not.be.visible');
                cy.get('.btn-buy').should('not.be.disabled');
            });
        });
    });
});
