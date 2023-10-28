const { Module } = Shopware;

import PrivilegesService from '@administration/app/service/privileges.service';

describe('swag-social-shopping', () => {
    beforeAll(() => {
        Shopware.Service().register('privileges', () => new PrivilegesService());
        require('../../src/main.js');
    });

    it('should be registered as a module', () => {
        expect(Module.getModuleRegistry().get('swag-social-shopping')).toBeDefined();
    });
});
