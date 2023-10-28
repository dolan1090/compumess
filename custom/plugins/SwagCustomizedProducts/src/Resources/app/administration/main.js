import enGBSnippets from './snippet/en-GB.json';

const { Application } = Shopware;

Application.addInitializerDecorator('locale', (localeFactory) => {
    localeFactory.extend('en-GB', enGBSnippets);

    return localeFactory;
});
