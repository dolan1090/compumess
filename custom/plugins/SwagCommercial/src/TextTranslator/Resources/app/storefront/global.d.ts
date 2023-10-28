/**
 * @package inventory
 */

// Type definitions for TextTranslator
// Project: SwagCommercial, Storefront area

interface Review {
    id: string;
    originalTitle: string;
    originalContent: string;
    originalComment: string|null;
    translatedTitle?: string;
    translatedContent?: string;
    translatedComment?: string|null;
    translatedLanguageName?: string|null;
    isTranslated: boolean;
}

interface ReviewTranslateOptions {
    snippets: {
        translateTo: string;
        revertToOriginal: string;
        translatedFrom: string;
        ourFeedback: string;
    };
}

interface StorefrontWindow extends Window {
    router: {
        'frontend.product.review.translate': string;
    };
}

declare module "src/plugin-system/plugin.class" {
    const Plugin:any;
    export = Plugin;
}

declare module "src/service/http-client.service" {
    const HttpClient:any;
    export = HttpClient;
}

declare module "src/utility/loading-indicator/element-loading-indicator.util" {
    const ElementLoadingIndicatorUtil:any;
    export = ElementLoadingIndicatorUtil;
}
