<?php declare(strict_types=1);

namespace ScDB2BInquiry;

use Shopware\Core\Content\MailTemplate\Aggregate\MailTemplateType\MailTemplateTypeEntity;
use Shopware\Core\Content\MailTemplate\MailTemplateEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\CustomField\CustomFieldTypes;

class ScDB2BInquiry extends Plugin
{
    final const MAIL_TEMPLATE_INQUIRY_NAME = 'scd.inquiryform';
    final const INQUIRY_CUSTOM_FIELD_SET_NAME = 'scd_b2b_inquiry_basket_set_customergroup';
    final const INQUIRY_CUSTOMERGROUP_CUSTOM_FIELD_SET_NAME = 'scd_b2b_inquiry_basket_set_customergroup';

    public function install(InstallContext $installContext): void
    {
        $mailTemplateRepository = $this->container->get('mail_template.repository');
        $mailTemplateTypeRepository = $this->container->get('mail_template_type.repository');

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('technicalName', self::MAIL_TEMPLATE_INQUIRY_NAME));
        $templateType = $mailTemplateTypeRepository->search($criteria,  $installContext->getContext())->first();

        if ( $templateType instanceof MailTemplateTypeEntity) {
            return;
        }

        $mailTemplateRepository->create([
            [
                'systemDefault' => false,
                'translations' => [
                    [
                        'languageId' => Defaults::LANGUAGE_SYSTEM,
                        'subject' => 'New inquiry',
                        'description' => 'Mail template send to shop owner with inquiry products.',
                        'senderName' => '{{ salesChannel.name }}',
                        'contentPlain' => 'Hallo Admin,
                                    Es gibt eine neue Produktanfrage!
                                    
                                    Vorname: {{ data.firstName }}
                                    Nachname: {{ data.lastName }}
                                    Telefon: {{ data.phone }}
                                    E-Mail: {{ data.email }}
                                    Firma: {{ data.company }}
                                    Abteilung: {{ data.department }}
                                    Kundennummer: {{ data.customernumber }}
                                    Kommentar: {{ data.comment }}
                                    Anfrageprodukte: 
                                    
                                        {% for product in data.products %}
                                            {{ product.productQuantity }}x {{ product.productLabel }} ({{ product.productNumber }})
                                        {% endfor %}
                                    
                                    ',
                        'contentHtml' => 'Hallo Admin,<br><br>
                                    Es gibt eine neue Produktanfrage!<br><br>
                                    
                                    <strong>Vorname: </strong>{{ data.firstName }}<br>
                                    <strong>Nachname: </strong>{{ data.lastName }}<br>
                                    <strong>Telefon: </strong>{{ data.phone }}<br>
                                    <strong>E-Mail: </strong>{{ data.email }}<br>
                                    <strong>Firma: </strong>{{ data.company }}<br>
                                    <strong>Abteilung: </strong>{{ data.department }}<br>
                                    <strong>Kundennummer: </strong>{{ data.customernumber }}<br>
                                    <strong>Kommentar: </strong>{{ data.comment }}<br><br>
                                    <strong>Anfrageprodukte: </strong><br>
                                    <ul>
                                        {% for product in data.products %}
                                            <li>{{ product.productQuantity }}x {{ product.productLabel }} ({{ product.productNumber }})</li>
                                        {% endfor %}
                                    </ul>
                        '
                    ]
                ],
                'mailTemplateType' => [
                    'technicalName' => self::MAIL_TEMPLATE_INQUIRY_NAME,
                    'availableEntities' => [
                        'salesChannel' => 'sales_channel'
                    ],
                    'translations' => [
                        [
                            'languageId' => Defaults::LANGUAGE_SYSTEM,
                            'name' => 'Inquiry form'
                        ],
                    ]
                ]
            ]
        ],  $installContext->getContext());

        /**
         * @var EntityRepository $customFieldRepository
         */
        $customFieldRepository = $this->container->get('custom_field.repository');
        /**
         * @var EntityRepository $customFieldSetRepository
         */
        $customFieldSetRepository = $this->container->get('custom_field_set.repository');

        // Check if custom fields already exist
        $result = $customFieldRepository->searchIds(
            (new Criteria())->addFilter(
                new EqualsAnyFilter(
                    'name',
                    [
                        'scd_activate_inquiry_basket',
                        'scd_customergroup_list',
                    ]
                )
            ),
             $installContext->getContext()
        );
        if ($result->getTotal() <= 0) {
            $customFieldScD = [
                [
                    'name' => 'scd_activate_inquiry_basket',
                    'type' => CustomFieldTypes::BOOL,
                    'config' => [
                        'type' => 'switch',
                        'label' => [
                            'en-GB' => 'Should an inquiry basket be displayed for this article?',
                            'de-DE' => 'Soll für diesen Artikel ein Anfragekorb angezeigt werden?',
                        ],
                        'componentName' => 'sw-field',
                        'customFieldType' => 'switch',
                        'customFieldPosition' => 10,
                    ],
                ]
            ];

            // Add Purified customField Sets
            $customFieldSetRepository->create(
                [
                    [
                        'name' => self::INQUIRY_CUSTOM_FIELD_SET_NAME,
                        'config' => [
                            'label' => [
                                'en-GB' => 'Shopcorn Digital - B2B inquiry basket',
                                'de-DE' => 'Shopcorn Digital - B2B Anfragekorb',
                            ],
                            'translated' => true,
                        ],
                        'relations' => [
                            [
                                'id' => Uuid::randomHex(),
                                'entityName' => 'product',
                            ],
                        ],
                        'customFields' => $customFieldScD,
                    ]
                ],
                 $installContext->getContext()
            );
        }

        $result = $customFieldRepository->searchIds(
            (new Criteria())->addFilter(
                new EqualsAnyFilter(
                    'name',
                    [
                        'scd_customergroup_hideinquiry'
                    ]
                )
            ),
             $installContext->getContext()
        );
        if ($result->getTotal() <= 0) {
            $customFieldScDCustomergroup = [
                [
                    'name' => 'scd_customergroup_hideinquiry',
                    'type' => CustomFieldTypes::BOOL,
                    'config' => [
                        'type' => 'switch',
                        'label' => [
                            'en-GB' => 'Doesnt see any inquiry article',
                            'de-DE' => 'Sieht keine Anfrageartikel',
                        ],
                        'componentName' => 'sw-field',
                        'customFieldType' => 'switch',
                        'customFieldPosition' => 10,
                    ],
                ]
            ];

            // Add Purified customField Sets
            $customFieldSetRepository->create(
                [
                    [
                        'name' => self::INQUIRY_CUSTOMERGROUP_CUSTOM_FIELD_SET_NAME,
                        'config' => [
                            'label' => [
                                'en-GB' => 'Shopcorn Digital - B2B inquiry basket (Customergroup)',
                                'de-DE' => 'Shopcorn Digital - B2B Anfragekorb (Kundengruppe)',
                            ],
                            'translated' => true,
                        ],
                        'relations' => [
                            [
                                'id' => Uuid::randomHex(),
                                'entityName' => 'customer_group',
                            ],
                        ],
                        'customFields' => $customFieldScDCustomergroup,
                    ]
                ],
                 $installContext->getContext()
            );
        }
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        if ($uninstallContext->keepUserData()) {
            return;
        }

        //Remove customFields
        /**
         * @var EntityRepository $customFieldSetRepository
         */
        $customFieldSetRepository = $this->container->get('custom_field_set.repository');
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('name', [self::INQUIRY_CUSTOM_FIELD_SET_NAME,self::INQUIRY_CUSTOMERGROUP_CUSTOM_FIELD_SET_NAME]));

        $customFieldSetIds = $customFieldSetRepository->searchIds($criteria, $uninstallContext->getContext());
        if ($customFieldSetIds->getTotal() === 0) {
            return;
        }

        $customFieldSetIds = \array_map(static fn($id) => ['id' => $id], $customFieldSetIds->getIds());
        $customFieldSetRepository->delete($customFieldSetIds, $uninstallContext->getContext());

        //Remove Email Template
        $mailTemplateRepository = $this->container->get('mail_template.repository');
        $mailTemplateTypeRepository = $this->container->get('mail_template_type.repository');

        $criteria = new Criteria();
        $criteria->addAssociation('mailTemplateType');
        $criteria->addFilter(new EqualsFilter('mailTemplateType.technicalName', self::MAIL_TEMPLATE_INQUIRY_NAME));
        $templates = $mailTemplateRepository->search($criteria, $uninstallContext->getContext());

        if ($templates->count() <= 0) {
            return;
        }

        $mailTemplateIds = [];
        $mailTemplateTypeIds = [];

        /** @var MailTemplateEntity $mailTemplate */
        foreach ($templates->getElements() as $mailTemplate) {
            $mailTemplateIds[] = ['id' => $mailTemplate->getId()];

            if (!in_array($mailTemplate->getMailTemplateTypeId(), $mailTemplateTypeIds)) {
                $mailTemplateTypeIds[] = ['id' => $mailTemplate->getMailTemplateTypeId()];
            }
        }

        if (!empty($mailTemplateIds)) {
            $mailTemplateRepository->delete($mailTemplateIds, $uninstallContext->getContext());
        }

        if (!empty($mailTemplateTypeIds)) {
            $mailTemplateTypeRepository->delete($mailTemplateTypeIds, $uninstallContext->getContext());
        }

        parent::uninstall($uninstallContext);
    }
}
