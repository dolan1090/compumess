# Next release

# 4.2.0
- CUS-225 - Fixed an issue of the validation of excluded combinations in the Storefront
- CUS-539 - Fixed customized product line item unit price calculation
- CUS-648 - Fixed an issue where the discount is granted only once
- NEXT-29020 - Integrated Vue3 compatibility into SwagCustomizedProducts

# 4.1.0
- CUS-543 - Migration Custom Product processing

# 4.0.2
- CUS-619 - Fixed "Self-collapsing options" function for select option types
- CUS-622 - Add missing icons in administration

# 4.0.1
- CUS-607 - Fixed display error in step-by-step mode

# 4.0.0
- CUS-562 - Ensure compatibility to Shopware Version 6.5.0.0

# 3.4.5
- CUS-540 - Fixes an issue where prices in PDP layouts are wrapped incorrectly
- CUS-283 - Fixed design in admin area concerning the excluded combinations of options
- CUS-545 - Enhances SEO and fixes mobile styling problem with pricing

# 3.4.4
- CUS-538 - Fixed an issue where too many elements were loaded

# 3.4.3
- CUS-526 - Fixed an issue where a migration requires elevated privileges 

# 3.4.2
- CUS-278 - Fixed an issue where discounts were calculated wrong when using relative surcharges.
- CUS-279 - Fixed an issue where discounts were applied incorrectly when using certain rules.
- CUS-514 - Improved the product detail page loading speed by reducing the number of database calls. A significant improvement can be seen especially in the image selection options. ([Stefan Poensgen](https://github.com/stefanpoensgen))

# 3.4.1
- CUS-268 - Fixed an issue where discounts were doubled if a Custom Product was in basket

# 3.4.0
- CUS-213 - Fixed an issue when using discounts with Custom Products
- CUS-251 - Fixes an issue where order details wrongly showed a warning about deleted products

# 3.3.0
- CUS-207 - Image Uploads now allows exclusions of defined file types
- CUS-228 - ID is not shown in the cart anymore when using products with selection fields
- DYN-18 - The price detail box now considers cart rules

# 3.2.0
- CUS-216 - Use the new view of nested line items in storefront and admin
- CUS-217 - Displays entered text correctly in the invoice, delivery note and cancellation documents again

# 3.1.1
- CUS-144 - Fixes an issue where default values of multiple selection fields could not be shared or edited correctly
- CUS-197 - Adds support for nested line items in the order detail page
- CUS-202 - Displays the file name of uploaded files in the invoice, delivery note and cancellation documents
- CUS-203 - The file upload now respects the maximum amount of files while adding via drag and drop
- CUS-209 - Added events for file upload success, failure and removal
- CUS-209 - Correctly enables & disables next button, due file or image upload's validity
- CUS-212 - Correctly enables & disables next button, due date or time field's validity
- CUS-219 - Improved plugin performance

# 3.1.0
- CUS-146 - The next button in the step-by-step mode are disabled by default for required options
- CUS-152 - Disables the "configure product" button initially to prevent issues rendering the configuration container on the product detail page
- CUS-188 - Fixes a problem with the emails
- CUS-193 - Fixes the display of one-time surcharges in emails and documents
- CUS-194 - Older versions of templates are now cleaned up in the background
- CUS-200 - Adds the support for the `*.bmp`, `*.eps`, `*.svg`, `*.tif` & `*.webp` file types to the storefront image upload component

# 3.0.0
- CUS-134 - Implement wishlist for Custom Products
- CUS-158 - Compatibility for Shopware 6.4
- CUS-165 - Move Custom Products assignment card to specifications tabs
- CUS-183 - Implement Custom Products for CMS product page & CMS buy box element

# 2.11.2
- CUS-177 - Fixes variant information in documents and removes truncation for product numbers

# 2.11.1
- CUS-180 - Fixes a problem that caused the plugin to be incorrectly marked compatible with Shopware 6.4

# 2.11.0
- CUS-23 - Introduce clear buttons for option types: `textarea`, `date field` and `time field`
- CUS-34 - Order confirmation now shows items in correct order
- CUS-102 - Invoice and Delivery notes now display the value entered by the user for most options like in checkout
- CUS-116 - Fix truncating length of the configuration text in the storefront
- CUS-127 - Fix price box updating on quantity change
- CUS-133 - Disable buy button if required radio fields are not checked in the storefront
- CUS-135 - Fixes incorrect display of surcharges with same label in the storefront
- CUS-137 - Fixes unit price display for non Custom Product order positions
- CUS-145 - Improve media loading on the detail page of the admin module
- CUS-147 - Fixes editing of regular order positions of an order without customized product positions
- CUS-155 - Fixes a problem duplication templates with associated exclusions
- CUS-157 - Step-by-step mode starts from the first step as soon as the page is reloaded
- CUS-160 - Order confirmation now display the value entered by the user for most options like in checkout

# 2.10.0
- CUS-150 - Introduce routes for store API and ensure compatibility with Shopware 6.3.5.1

# 2.9.1
- CUS-150 - Improve compatibility to wish list feature

# 2.9.0
- CUS-107 - Fixes an issue where Custom Products data remained in the product table
- CUS-136 - Products cannot be added directly to the cart anymore, while they have an active template assigned

# 2.8.0
- CUS-46 - Added the ability to select a default value for image, color and normal selects
- CUS-63 - Prevents reordering of products that have become Custom Products in the meantime
- CUS-83 - Added error messages to number fields and made their default value not required
- CUS-93 - Added message to indicate empty product configurations in the order module
- CUS-95 - Searching for options doesn't affect the exclusion list anymore
- CUS-97 - Fixes multiple inheritance of the line items overview in the order module
- CUS-99 - Fixes installation with primary key requirement enabled in database
- CUS-108 - Fixes installation on systems without English language
- CUS-111 - Datefields now work as required options
- CUS-112 - Fixes incorrect error messages with file and image upload
- PPI-174 - Cart and order items are now correctly submitted to PayPal

# 2.7.0
- CUS-35 - Placeholder for Custom Product options are now translatable. Fixes language fallback for select field values
- CUS-38 - Added error messages to file-uploads and fixed the buy-button not respecting the maximum amount of files
- CUS-81 - Added ACL privileges to the Custom Product module
- CUS-84 - Standardized the field alignments of Custom Product option properties
- CUS-86 - Fixes database migrations

# 2.6.5
- CUS-86 - Fixes database migrations

# 2.6.4
- CUS-16 - Fixes a saving problem, when editing Custom Product option, which were initially created without any configuration
- CUS-55 - Fixes deleting of Custom Product templates
- CUS-60 - Fixes the positioning and table behaviour of the option types in the Custom Products module
- CUS-61 - Added display of variation specification in the cart, when a Custom Products Template is applied
- CUS-62 - Changed behaviour, that options are reacting to a key press instead of "change"
- CUS-78 - Changed notification titles to Shopware default

# 2.6.3
- CUS-19 - Fixes the translation of price box for multilingual saleschannels
- CUS-57 - Added variant specification display in order position overview
- CUS-66 - Fixes writing of products without Custom Products data

# 2.6.2
- CUS-32 - Improve validation when updating options
- CUS-49 - Order numbers are displayed correctly for products
- CUS-50 - Fixes item link title in cart
- CUS-58 - Fixes loading of Custom Products on the product detail page

# 2.6.1
- CUS-3 - Position of a border in the account section got adjusted

# 2.6.0
- CUS-6 - Added a setting that prompts the customer to confirm their entries

# 2.5.1
- CUS-29 - Custom Products can now be installed with different default languages

# 2.5.0
- CUS-1 - Upload option types can now be excluded
- CUS-5 - Replace third party WYSIWYG editor with our own solution
- CUS-10 - Improved media and error handling with Shopware's best practice
- CUS-12 - Customers can now share their configuration from the product detail page and edit it through the cart process
- CUS-14 - HTML-Editor formatting is now visible in the cart preview
- CUS-15 - Buying of products with an empty Custom Products template is now possible
- CUS-20 - Removes ability to jump from one page to another using the keyboard in step-by-step mode
- CUS-22 - Sending test mails from the admin is now possible with Custom Products installed
- CUS-24 - Added border around color selection for better visibility

# 2.4.0
- PT-11483 - Time field option min/max configuration fixed
- PT-11563 - Sorting the option values is now possible
- PT-11912 - Storefront snippets now get auto registered
- PT-11918 - Shopware 6.3 compatibility
- PT-11950 - The option value tree can be scrolled now
- PT-11954 - Date / time fields show the correct value after saving again

# 2.3.0
- PT-11308 - Removed numberfield configuration for "decimal places", which was in contradiction to the "step size" configuration
- PT-11310 - Time and date validation for the option types date field and time selection field implemented
- PT-11370 - In storefront by clicking on an image in the image selection it will be displayed in a full screen view 
- PT-11452 - Implemented optional option collapsing, if the current option is valid and clicking into a new one
- PT-11621 - Failed orders which contain one or more Custom Products can now be edited in the account
- PT-11719 - Duplicating the product template is deactivated if the template has not yet been saved.
- PT-11775 - Required options of the type file and image upload no longer block ordering a Custom Product
- PT-11823 - The HTML editor is now correctly vertical aligned in step by step mode
- PT-11840 - The "add product" button is back in the order module
- PT-11881 - Compatibility for Safari and Internet Explorer 11
- PT-11897 - File and image upload now return a correct error state when exceeding max file size or amount 

# 2.2.0
- PT-11172 - Adjusted Custom Products option modal box to fixed size
- PT-11288 - Relative surcharges are now listed correctly in the order confirmation mail
- PT-11303 - The calculated custom product price, as well as the summary of the selected options, will be broken down right above the buy button
- PT-11312 - Required options of the custom product are automatically unfolded on the product detail page
- PT-11359 - One time relative surcharges are now calculated correctly
- PT-11466 - Ensures compatibility between promotions and Customized Products
- PT-11632 - Added navigation for elements that cannot be combined due to an exclusion
- PT-11773 - Custom Products with unfilled required options can no longer be placed in the cart
- PT-11774 - The step by step mode no longer cuts off the configuration for image and file uploads
- PT-11868 - Implemented multi select validation for step-by-step mode

# 2.1.0
- PT-11476 - Provides Store API endpoints
- PT-11799 - Customer without orders can use the overview again

# 2.0.0
- PT-11724 - Deletes storefront uploads on uninstall
- PT-11743 - Ensures the extensibility of the SalesChannelProductCriteria

# 1.3.3
- PT-11698 - Adjusted max display amount of options' selections
- PT-11738 - Fixes uploads in the storefront
- PT-11739 - Fixes plugin installation for shops where the languages German and/or English are missing

# 1.3.2
- PT-11427 - Fixes displaying of absolute surcharges of template options and its values with non-default currencies
- PT-11587 - Dokumentenerstellung verbessert
- PT-11701 - Fixes duplication of templates

# 1.3.1
- PT-11652 - Fixes an error where the upload endpoint is incorrectly
- PT-11651 - Fixes a bug where certain file names could not be uploaded
- PT-11164 - Fixes bug which prevented uploading the same file twice in a row

# 1.3.0
- PT-11607 - Shopware 6.2 compatibility
- PT-11474 - Implemented duplication of custom products
- PT-11426 - Implemented exclusion handling
- PT-10937 - Implemented file and image upload

# 1.2.1
- NTR - Fix account order overview

# 1.2.0
- PT-10906 - Adds step-by-step mode
- PT-11306 - Adds option values count column which outputs the amount of assigned option values
- PT-11309 - Changes naming of "order number" to "option product number" in options
- PT-11314 - Improves compatibility with QuickView of CMS extension plugin
- PT-11316 - Adds assignment information box
- PT-11355 - Line breaks are now visible in the cart
- PT-11422 - Adds HTML editor option
- PT-11441 - Fixes the german translation of the description field of an option
- PT-11454 - Fixes indentation of selection options
- PT-11482 - Removes placeholder field from the number field option
- PT-11496 - Prices will only be displayed next to the value when the option is a selection
- PT-11554 - Fixes HTML editor configuration possibilities

# 1.1.0
- PT-10720 - Improves error handling in option modal
- PT-11110 - Improves extension of documents
- PT-11226 - Fixes link to product detail page from order module
- PT-11227 - Adds surcharge information to orders of Custom Products and fixes the price in its parent position listing
- PT-11236 - Solves an issue with validation of colorpicker
- PT-11249 - Solves an issue where price rules couldn't be added
- PT-11250 - Solves an issue with invalid selection options
- PT-11253 - Replaced checkboxes with toggle switches
- PT-11255 - Fixes required field of selection options in storefront
- PT-11278 - Applys style fixes for Storefront surcharges
- PT-11279 - Extension of the order module optimized
- PT-11280 - Adds the new cart layout to order history
- PT-11282 - Hide list prices in option modal
- PT-11286 - Fixes editing of the name of tree items. Adds a button to add a subelement
- PT-11289 - Description for option types is now displayed in the storefront
- PT-11290 - Solves an issue with display of surcharge in cart
- PT-11302 - Adds imageselect to option types
- PT-11307 - Removed the unnecessary required option from the checkbox option type
- PT-11311 - Add expand and shrink function to text options in cart
- PT-11315 - Optimizes placeholders
- PT-11352 - Implements imageselect renderer for the order overview
- PT-11362 - Option surcharges are now calculated via actual price of quantity 1

# 1.0.0
- PT-11144 - Solves an issue where Custom Products wouldn't work with product variants
- PT-11145 - Solves an issue where required options wouldn't get validated
- PT-11149 - Enables reordering of Custom Products in the storefront account
- PT-11150 - Introduce error handling to the administration
- PT-11151 - Solves an issue where the empty state of the option listing disappears
- PT-11154 - Solves an issue where same configured products wouldn't get grouped
- PT-11162 - Adds the new cart layout
- PT-11180 - Solves an issue where Custom Products could be bought without configuring them
- PT-11198 - Solves an issue with order document creation
- PT-11218 - Enhances managing the translations of options
- PT-11219, PT-11208, PT-11159 - Storefront style optimizations
- PT-11220 - Solves an issue with the order confirmation mail
- PT-11236 - Optimizes option handling in the storefront

# 0.9.0
- Initial Custom Products release for Shopware 6
