import { shallowMount } from '@vue/test-utils';
import '../../src/mixin/swag-customized-products-option.mixin';

const { Component } = Shopware;
let wrapper;

describe('src/mixin/swag-customized-option.mixin.js', () => {
    // Register new component to test the mixin
    Component.register('test-component', {
        template: `<div v-html="translateOption('foobar')"></div>`,
        mixins: [
            Shopware.Mixin.getByName('swag-customized-products-option')
        ]
    });

    // Mount the new component before each test case
    beforeEach(async () => {
       wrapper = shallowMount(await Component.build('test-component'), {
           mocks: {
               $tc: key => key
           }
       });
    });

    // Destroy the component after each test case
    afterEach(() => {
        wrapper.destroy();
    });

    it('should be a Vue.js component after using the mixin', () => {
        expect(wrapper.exists()).toBeTruthy();
    });

    it('should provide the method "translateOption" to the component using the mixin', () => {
        expect(wrapper.vm.hasOwnProperty('translateOption')).toBeTruthy();
        expect(wrapper.text()).toBe('swag-customized-products.optionTypes.foobar');
    });
});
