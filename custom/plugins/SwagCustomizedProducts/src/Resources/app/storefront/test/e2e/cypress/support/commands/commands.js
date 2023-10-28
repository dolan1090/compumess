const uuid = require('uuid/v4');
const ProductFixture = global.ProductFixtureService;

Cypress.Commands.add('createViaAdminApi', (data) => {
    return cy.requestAdminApi(
        'POST',
        `/api/${data.endpoint}?response=true`,
        data
    ).then((responseData) => {
        return responseData;
    });
});

/**
 * Create custom product fixture using Shopware API at the given endpoint
 * @memberOf Cypress.Chainable#
 * @name createProductFixture
 * @function
 * @param {Object} [userData={}] - Options concerning creation
 * @param [String] [templateFixtureName = 'product'] - Specifies the base fixture name
 */
Cypress.Commands.add('createCustomProductFixture', (userData = {}, templateFixtureName = 'product', categoryName = 'Confirm input catalogue') => {
    const fixture = ProductFixture;

    return cy.fixture(templateFixtureName).then((result) => {
        return Cypress._.merge(result, userData);
    }).then((data) => {
        return fixture.setProductFixture(data, categoryName);
    });
});

Cypress.Commands.add('patchViaAdminApi', (endpoint, data) => {
    return cy.requestAdminApi(
        'PATCH',
        `/api/${endpoint}?response=true`,
        data
    ).then((responseData) => {
        return responseData;
    });
});

Cypress.Commands.add('createCustomerFixtureStorefront', (userData) => {
    const addressId = uuid().replace(/-/g, '');
    const customerId = uuid().replace(/-/g, '');
    let customerJson = {};
    let customerAddressJson = {};
    let finalAddressRawData = {};
    let countryId = '';
    let groupId = '';
    let paymentId = '';
    let salesChannelId = '';
    let salutationId = '';

    return cy.fixture('customer').then((result) => {
        customerJson = Cypress._.merge(result, userData);

        return cy.fixture('customer-address')
    }).then((result) => {
        customerAddressJson = result;

        return cy.searchViaAdminApi({
            endpoint: 'country',
            data: {
                field: 'iso',
                value: 'DE'
            }
        });
    }).then((result) => {
        countryId = result.id;

        return cy.searchViaAdminApi({
            endpoint: 'payment-method',
            data: {
                field: 'name',
                value: 'Invoice'
            }
        });
    }).then((result) => {
        paymentId = result.id;

        return cy.searchViaAdminApi({
            endpoint: 'sales-channel',
            data: {
                field: 'name',
                value: 'Storefront'
            }
        });
    }).then((result) => {
        salesChannelId = result.id;

        return cy.searchViaAdminApi({
            endpoint: 'customer-group',
            data: {
                field: 'name',
                value: 'Standard customer group'
            }
        });
    }).then((result) => {
        groupId = result.id;

        return cy.searchViaAdminApi({
            endpoint: 'salutation',
            data: {
                field: 'displayName',
                value: 'Mr.'
            }
        });
    }).then((salutation) => {
        salutationId = salutation.id;

        finalAddressRawData = Cypress._.merge({
            addresses: [{
                customerId: customerId,
                salutationId: salutationId,
                id: addressId,
                countryId: countryId
            }]
        }, customerAddressJson);
    }).then(() => {
        return Cypress._.merge(customerJson, {
            salutationId: salutationId,
            defaultPaymentMethodId: paymentId,
            salesChannelId: salesChannelId,
            groupId: groupId,
            defaultBillingAddressId: addressId,
            defaultShippingAddressId: addressId
        });
    }).then((result) => {
        return Cypress._.merge(result, finalAddressRawData);
    }).then((result) => {
        return cy.createViaAdminApi({
            endpoint: 'customer',
            data: result
        });
    });
});


Cypress.Commands.add('requestStoreApiAccessToken', () => {
    return cy.searchViaAdminApi({
        endpoint: 'sales-channel',
        data: {
            field: 'name',
            value: 'Storefront'
        }
    }).then((response) => {
        return response.attributes.accessKey;
    });
});

Cypress.Commands.add('requestStoreApiContextToken', (accessKey) => {
    return cy.request({
        method: 'GET',
        url: '/store-api/context',
        headers: {
            'sw-access-key': accessKey
        }
    }).then((response) => {
        return response.body.token;
    });
});

Cypress.Commands.add('requestStoreApiAccountLogin', ({ username, password, accessKey, contextToken }) => {
    return cy.request({
        method: 'POST',
        url: '/store-api/account/login',
        headers: {
            'sw-access-key': accessKey,
            'sw-context-toke': contextToken
        },
        body: {
            username,
            password
        }
    }).then((response) => {
        return response.contextToken;
    });
});
