<?php

/* this subscriber file is needed to get the variables in scss - subscriber added in services.xml */

declare(strict_types = 1);

namespace Eseom\OrderForm\Subscriber;

use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Theme\Event\ThemeCompilerEnrichScssVariablesEvent;
//use Shopware\Storefront\Event\ThemeCompilerEnrichScssVariablesEvent;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PluginVariablesSubscriber implements EventSubscriberInterface {

    public static function getSubscribedEvents(): array {
        return [
            ThemeCompilerEnrichScssVariablesEvent::class => 'onAddVariables'
        ];
    }

    /**
     * @var SystemConfigService
     */
    protected $systemConfig;

    // add the `SystemConfigService` to your constructor
    public function __construct(SystemConfigService $systemConfig) {
        $this->systemConfig = $systemConfig;
    }

    public function onAddVariables(ThemeCompilerEnrichScssVariablesEvent $event) {
        $configFields = $this->systemConfig->get('EseomOrderForm.config', $event->getSalesChannelId());

        if (isset($configFields) && sizeof($configFields) > 0) {
            foreach ($configFields as $key => $value) {
                // Convert `customVariableName` to `custom-variable-name`
                $variableName = str_replace('_', '-', (new CamelCaseToSnakeCaseNameConverter())->normalize($key));
                
                if($key === 'eseomOrderFormImportExportDelimiter'){
                    
                } else if (!empty($value) && is_string($value) && !strtotime($value)) {
                    $event->addVariable($variableName, $value);
                } else if (!empty($value) && is_int($value)) {
                    //params => name, value, sanitize --> if we do not add false the theme throws an error when compiling
                    $event->addVariable($variableName, '"' . $value . '"', false);
                } else if (empty($value)) {
                    //params => name, value, sanitize --> if we do not add false the theme throws an error when compiling
                    $event->addVariable($variableName, '"0"', false);
                }
            }
        }
    }

}
