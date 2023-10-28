import Plugin from 'src/plugin-system/plugin.class';
import HttpClient from 'src/service/http-client.service';
import Iterator from 'src/helper/iterator.helper';

export default class OrderForm extends Plugin {
    init() {
        this.editedSkuInputArr = [];
        this.editedQtyInputArr = [];
        this.boundSkuInputEventHandler;
        this.boundQtyInputEventHandler;
        this.boundInfoBtnEventHandler;
        this.boundDeleteBtnEventHandler;
        this.boundCloseFoundProductsBtnEventHandler;
        this.boundFoundProductBtnEventHandler;
        this.boundChooseVariantBtnEventHandler;
        this.client = new HttpClient();
        this.offcanvasCartActionBlocked = true;
        this.showStarBehindPrices = false;
// Start searching products
        this.minSearchLength = 3;
        this.showSearchSuggestions = true;
        this.currentSkuInputChangedElem;
        this.productSuggestionClicked = false;
        let parentThis = this;

        const loadingModal = document.getElementById('eseom-order-form-loading-modal');
        parentThis.loadingModalInstance = new bootstrap.Modal(loadingModal, {
            show: true,
            backdrop: 'static',
            keyboard: false
        });
        
        const chooseVariantModal = document.getElementById('eseom-order-form-choose-variant-modal');
        parentThis.chooseVariantModalInstance = new bootstrap.Modal(chooseVariantModal, {
            show: true,
            backdrop: 'static',
            keyboard: false
        });
        
        const infoModal = document.getElementById('eseom-order-form-info-modal');
        parentThis.infoModalInstance = new bootstrap.Modal(infoModal, {
            show: true,
            backdrop: 'static',
            keyboard: false
        });
        
        const errorModal = document.getElementById('eseom-order-form-error-modal');
        parentThis.errorModalInstance = new bootstrap.Modal(errorModal, {
            show: true,
            backdrop: 'static',
            keyboard: false
        });
        
        const successModal = document.getElementById('eseom-order-form-success-modal');
        parentThis.successModalInstance = new bootstrap.Modal(successModal, {
            show: true,
            backdrop: 'static',
            keyboard: false
        });

        document.querySelector('#eseom-order-form-add-to-cart-btn').addEventListener('click', this.addToCart.bind(this));

        document.querySelector('#eseom-order-form-add-position').addEventListener('click', () => {
            parentThis.addNewPosition('', 1);

            parentThis.bindElements();
        });

        parentThis.boundSkuInputEventHandler = parentThis.onSkuInputChange.bind(parentThis);
        parentThis.boundQtyInputEventHandler = parentThis.onQuantityInputChange.bind(parentThis);
        parentThis.boundInfoBtnEventHandler = parentThis.onInfoBtnClicked.bind(parentThis);
        parentThis.boundDeleteBtnEventHandler = parentThis.onDeleteBtnClicked.bind(parentThis);
        parentThis.boundCloseFoundProductsBtnEventHandler = parentThis.onCloseFoundProductsBtnClicked.bind(parentThis);
        parentThis.boundFoundProductBtnEventHandler = parentThis.onFoundProductClicked.bind(parentThis);
        parentThis.boundChooseVariantBtnEventHandler = parentThis.onChooseVariantBtnClicked.bind(parentThis);

        parentThis.bindElements();

        if (document.querySelector('#eseom-order-form-show-star-behind-prices')) {
            parentThis.showStarBehindPrices = true;
        }

        document.querySelector('#eseom-order-form-clear-positions').addEventListener('click', (e) => {
            Array.from(document.querySelectorAll(".eseom-order-form-sku-input")).forEach(thisElem => {
                thisElem.value = "";
                parentThis.handleSkuInputChange(thisElem);
            });
            parentThis.downloadStatus();
        });

        /* Upload an order-list */
        document.querySelector('.eseom-order-form-import').addEventListener('click', (e) => {
            document.querySelector('.eseom-order-form-import-file.custom-file-input').click();
        });

        document.querySelector('.eseom-order-form-import-file.custom-file-input').addEventListener('change', parentThis.importFile.bind(parentThis));

        /* Download order-list */
        document.querySelector('.eseom-order-form-export').addEventListener('click', parentThis.exportFile.bind(parentThis));

        /* BEGIN - IF THERE ARE PRE-FILLED PRODUCTS DEFINED IN THE PRODUCT CONFIGURATION -> FETCH THE PRICES ON PAGE LOAD */
        parentThis.refreshAllPricesInTable();
        /* END - IF THERE ARE PRE-FILLED PRODUCTS DEFINED IN THE PRODUCT CONFIGURATION -> FETCH THE PRICES ON PAGE LOAD */

        document.querySelector('.header-cart[data-offcanvas-cart="true"]').addEventListener('click', (e) => {
            /* TO PREVENT THAT THE getPrice METHOD IS CALLED WHEN THE OFFCANVAS CART IS OPENED */
            parentThis.setOffcanvasCartActionBlocked(true);
        });

        /* BEGIN - RECALCULATE THE PRICES IF THE QUANTITY IN THE OFFCANVAS CART IS CHANGED */
        const plugin = window.PluginManager.getPluginInstanceFromElement(document.querySelector('[data-offcanvas-cart]'), 'OffCanvasCart');
        plugin.$emitter.subscribe('registerEvents', this.onOffcanvasCartChanged);
        /* END - RECALCULATE THE PRICES IF THE QUANTITY IN THE OFFCANVAS CART IS CHANGED */
    }

    importFile(event) {
        let parentThis = this;
        let eventObj = event.currentTarget;
        let orderFormTableBody = document.querySelector("#eseom-order-form-table > tbody");
        let filename = eventObj.value.length ? eventObj.value.replace(/C:\\fakepath\\/i, '') : "";
        if (filename !== '') {
            document.querySelector('.eseom-order-form-import-label').innerHTML = filename;
            let delimiter = ';';
            if (document.querySelector('#eseom-order-form-table').getAttribute('data-delimiter') === 'comma') {
                delimiter = ',';
            }
            var fileUpload = document.getElementById("upload-file");
            var regex = /^([a-zA-Z0-9\s_\\.\-:])+(.csv|.txt)$/;
            if (regex.test(fileUpload.value.toLowerCase())) {
                if (typeof (FileReader) != "undefined") {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        let rows = e.target.result.split("\n");
                        rows[0] = rows[0].replace(/\r/, '');
                        let header = rows[0].split(delimiter);
                        let productNrIndex = header.indexOf('ProductNr / EAN');
                        let quantityIndex = header.indexOf('Quantity');
                        let productRowsToUpdateObjects = [];
                        let productRowsToUpdateSkus = [];

                        if (productNrIndex === -1 || quantityIndex === -1) {
                            alert("CSV-Header is not correct");
                        } else {
                            // remove empty rows at the end of the table before
                            while (orderFormTableBody.rows.item(orderFormTableBody.rows.length - 1) && orderFormTableBody.rows.item(orderFormTableBody.rows.length - 1).querySelector('.eseom-order-form-sku-input').value === '') {
                                orderFormTableBody.rows.item(orderFormTableBody.rows.length - 1).remove();
                            }

                            for (let i = 1; i < rows.length; i++) {
                                rows[i] = rows[i].replace(/\r/, '');
                                if (rows[i].split(delimiter)[productNrIndex] !== '' && rows[i].split(delimiter)[quantityIndex] > 0) {
                                    let productNumber = rows[i].split(delimiter)[productNrIndex];
                                    let createdTr = parentThis.addNewPosition(productNumber, rows[i].split(delimiter)[quantityIndex]);
                                    if (!productRowsToUpdateSkus.includes(productNumber)) {
                                        productRowsToUpdateSkus.push(productNumber);
                                        productRowsToUpdateObjects.push(createdTr.querySelector('.eseom-order-form-sku-input'));
                                    }
                                }
                            }

                            parentThis.bindElements();

                            for (let j = 0; j < productRowsToUpdateObjects.length; j++) {
                                parentThis.showSearchSuggestions = false;
                                parentThis.getPrice(productRowsToUpdateObjects[j]);
                            }
                        }
                    }
                    reader.readAsText(fileUpload.files[0]);
                } else {
                    alert("This browser does not support HTML5.");
                }
            } else {
                alert("Please upload a valid CSV file.");
            }

            document.querySelector('.eseom-order-form-import-label').innerHTML = document.querySelector('.eseom-order-form-import-label').getAttribute('data-label');
            document.querySelector('#upload-file').value = '';
        } else {
            document.querySelector('.eseom-order-form-import-label').innerHTML = document.querySelector('.eseom-order-form-import-label').getAttribute('data-label');
        }
    }

    bindElements() {
        let parentThis = this;

        parentThis.bindInputs();
        parentThis.bindInfoBtns();
        parentThis.bindDeleteBtns();
    }

    exportFile(event) {
        let parentThis = this;
        let eventObj = event.currentTarget;
        if (eventObj.classList.contains('export-active')) {
            const headerArr = [
                'ProductNr / EAN',
                'ProductName',
                'Quantity'
            ];

            let delimiter = ';';
            if (document.querySelector('#eseom-order-form-table').getAttribute('data-delimiter') === 'comma') {
                delimiter = ',';
            }
            let csvData = headerArr.join(delimiter);

            Array.from(document.querySelectorAll("#eseom-order-form-table tbody tr")).forEach(thisElem => {
                let positionArr = [];
                if (thisElem.querySelector('.eseom-order-form-sku-input').value !== "") {
                    if (thisElem.querySelector('.eseom-order-form-price').innerHTML !== "-") {
                        positionArr.push(thisElem.querySelector('.eseom-order-form-sku-input').value);
                        let productNameAndEAN = thisElem.querySelector('.eseom-order-form-product-name-td').innerHTML;
                        let productName = productNameAndEAN.split("<br>")[0];
                        positionArr.push(productName);
                        positionArr.push(thisElem.querySelector('.eseom-order-form-quantity-input').value);

                        csvData += '\n' + positionArr.join(delimiter);
                    }
                }
            });

            const blob = new Blob([csvData], {type: 'text/csv'});
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');

            let d = new Date();
            let datestring = d.getFullYear() + ('0' + (d.getMonth() + 1)).slice(-2) + ('0' + d.getDate()).slice(-2) + "_" + d.getHours() + d.getMinutes() + d.getSeconds();

            a.setAttribute('href', url);
            a.setAttribute('download', eventObj.getAttribute('data-filename') + '_' + datestring + '.csv');
            a.click();
        }
    }

    addToCart(event) {
        let parentThis = this;
        let eventObj = event.currentTarget;
        /* BEGIN - POST DATA */
        let positionsArr = [];
        if (document.querySelector("#eseom-order-form-table tbody tr")) {
            Array.from(document.querySelectorAll("#eseom-order-form-table tbody tr")).forEach(thisElem => {
                if (thisElem.querySelector('.eseom-order-form-sku-input').value !== "") {
                    let positionsData = {};
                    positionsData.productNr = thisElem.querySelector('.eseom-order-form-sku-input').value;
                    positionsData.productQuantity = thisElem.querySelector('.eseom-order-form-quantity-input').value;
                    positionsArr.push(positionsData);
                }
            });
        }
        let ajaxPath = document.querySelector('#eseom-order-form-add-to-cart-ajax-path').value;
        let dataToObject = {};
        dataToObject.positions = positionsArr;
        let stringifiedData = JSON.stringify(dataToObject);
        parentThis.loadingModalInstance.show();
        parentThis.client.post(ajaxPath, stringifiedData, (responseData) => {
            var response_arr = JSON.parse(responseData);
            if (parseInt(response_arr.success) !== 1) {
                parentThis.showErrorModalWithMessage(response_arr.msg);
            } else {
                if (eventObj.getAttribute('data-success-action') === 'openModal') {
                    parentThis.showSuccessModalWithMessage(response_arr.msg);
                    /* BEGIN - SO IT ALSO UPDATES THE TOTAL PRICE BESIDE THE CART ICON IN THE HEADER IF WE SHOW A SUCCESS MODAL AND DO NOT OPEN THE OFFCANVAS CART */
                    const CartWidgetPluginInstances = window.PluginManager.getPluginInstances('CartWidget');
                    Iterator.iterate(CartWidgetPluginInstances, instance => instance.fetch());
                    /* END - SO IT ALSO UPDATES THE TOTAL PRICE BESIDE THE CART ICON IN THE HEADER IF WE SHOW A SUCCESS MODAL AND DO NOT OPEN THE OFFCANVAS CART */

                    parentThis.refreshAllPricesInTable();
                } else {
                    if (eventObj.getAttribute('data-success-action') === 'openOffcanvasCart') {
                        parentThis.showOffcanvasCart();
                    } else {
                        if (eventObj.getAttribute('data-success-action') === 'openCart') {
                            parentThis.showCart();
                        } else {
                            parentThis.showConfirm();
                        }
                    }
                }
            }
        });
        /* END - POST DATA */
    }

    /* Check if the download button should be active */
    downloadStatus() {
        let downloadActivated = false;

        Array.from(document.querySelectorAll("#eseom-order-form-table tbody tr")).forEach(thisElem => {
            if (thisElem.querySelector('.eseom-order-form-price').innerHTML !== "-") {
                if (!thisElem.querySelector('.eseom-order-form-price > div')) {
                    downloadActivated = true;
                }
            }
        });

        if (downloadActivated)
            document.querySelector('.eseom-order-form-export').classList.add('export-active');
        else
            document.querySelector('.eseom-order-form-export').classList.remove('export-active');
    }

    onOffcanvasCartChanged() {
        /* AS WE SUBSCRIBE A SHOPWARE EVENT "registerEvents" WE ARE IN ANOTHER CONTEXT/SCOPE AND NEED TO GET THE CURRENT PLUGIN TO BE ABLE TO CALL getPrice */
        const plugin = window.PluginManager.getPluginInstanceFromElement(document.querySelector('[data-eseom-order-form]'), 'EseomOrderForm');
        if (!plugin.getOffcanvasCartActionBlocked()) {
            plugin.refreshAllPricesInTable();
        } else {
            plugin.setOffcanvasCartActionBlocked(false);
        }
    }

    setOffcanvasCartActionBlocked(isBlocked) {
        this.offcanvasCartActionBlocked = isBlocked;
    }

    getOffcanvasCartActionBlocked() {
        return this.offcanvasCartActionBlocked;
    }

    bindInputs() {
        let parentThis = this;

        Array.from(document.querySelectorAll(".eseom-order-form-sku-input")).forEach(thisElem => {
            thisElem.removeEventListener('input', parentThis.boundSkuInputEventHandler);
            thisElem.addEventListener('input', parentThis.boundSkuInputEventHandler);
        });

        Array.from(document.querySelectorAll(".eseom-order-form-quantity-input")).forEach(thisElem2 => {
            thisElem2.removeEventListener('input', parentThis.boundQtyInputEventHandler);
            thisElem2.addEventListener('input', parentThis.boundQtyInputEventHandler);
        });
    }

    onSkuInputChange(event) {
        let parentThis = this;
        let thisObj = event.currentTarget;
        parentThis.handleSkuInputChange(thisObj);
        parentThis.currentSkuInputChangedElem = thisObj;
    }

    handleSkuInputChange(skuInputObj) {
        let parentThis = this;
        let editedSkuInputIndex = [...skuInputObj.parentElement.parentElement.parentElement.children].indexOf(skuInputObj.parentElement.parentElement);
        if (parentThis.editedSkuInputArr[editedSkuInputIndex] !== null && parentThis.editedSkuInputArr[editedSkuInputIndex] !== undefined) {
            clearTimeout(parentThis.editedSkuInputArr[editedSkuInputIndex]);
            parentThis.editedSkuInputArr[editedSkuInputIndex] = null;
        }
        parentThis.editedSkuInputArr[editedSkuInputIndex] = setTimeout(function () {
            if (skuInputObj.value !== skuInputObj.getAttribute('data-current-sku') && !parentThis.productSuggestionClicked) {
                parentThis.showSearchSuggestions = true;
            } else {
                parentThis.showSearchSuggestions = false;
            }
            parentThis.productSuggestionClicked = false;
            parentThis.getPrice(skuInputObj);
        }, 750);
    }

    onQuantityInputChange(event) {
        let parentThis = this;
        let thisObj = event.currentTarget;
        let skuInput = thisObj.parentElement.parentElement.querySelector('.eseom-order-form-sku-input');
        let editedQtyInputIndex = [...thisObj.parentElement.parentElement.parentElement.children].indexOf(thisObj.parentElement.parentElement);
        if (parentThis.editedQtyInputArr[editedQtyInputIndex] !== null && parentThis.editedQtyInputArr[editedQtyInputIndex] !== undefined) {
            clearTimeout(parentThis.editedQtyInputArr[editedQtyInputIndex]);
            parentThis.editedQtyInputArr[editedQtyInputIndex] = null;
        }
        parentThis.editedQtyInputArr[editedQtyInputIndex] = setTimeout(function () {
            parentThis.showSearchSuggestions = false;
            parentThis.getPrice(skuInput);
        }, 750);
    }

    getTotalPriceSum() {
        let totalSum = 0.0;

        Array.from(document.querySelectorAll("#eseom-order-form-table tbody tr .eseom-order-form-total-price")).forEach(thisElem => {
            if (thisElem.innerHTML !== "-" && thisElem.getAttribute("data-productTotalPrice") !== undefined) {
                totalSum += parseFloat(thisElem.getAttribute("data-productTotalPrice"));
            }
        });
        return totalSum;
    }

    bindInfoBtns() {
        let parentThis = this;

        Array.from(document.querySelectorAll(".eseom-order-form-action-btn-info")).forEach(thisElem => {
            thisElem.removeEventListener('click', parentThis.boundInfoBtnEventHandler);
            thisElem.addEventListener('click', parentThis.boundInfoBtnEventHandler);
        });
    }

    onInfoBtnClicked(event) {
        let parentThis = this;
        let thisObj = event.currentTarget;
        parentThis.loadingModalInstance.show();
        let productNumber = thisObj.parentElement.parentElement.querySelector('.eseom-order-form-sku-input').value;
        let productQuantity = thisObj.parentElement.parentElement.querySelector('.eseom-order-form-quantity-input').value;
        if (productQuantity === "") {
            productQuantity = 1;
            thisObj.parentElement.parentElement.querySelector('.eseom-order-form-quantity-input').value = productQuantity;
        }
        /* BEGIN - POST DATA */
        let ajaxPath = document.querySelector('#eseom-order-form-get-product-info-ajax-path').value;
        let dataToObject = {};
        dataToObject.productNumber = productNumber;
        dataToObject.productQuantity = productQuantity;
        dataToObject.navigationId = document.querySelector('#foundProductsDiv').getAttribute("data-navigationId");
        let stringifiedData = JSON.stringify(dataToObject);
        parentThis.client.post(ajaxPath, stringifiedData, (responseData) => {
            var response_arr = JSON.parse(responseData);
            if (parseInt(response_arr.success) !== 1) {
                parentThis.showErrorModalWithMessage(response_arr.msg);
            } else {
                document.querySelector('#eseom-order-form-info-modal .modal-body').innerHTML = "";
                let productName = response_arr.productName;
                let productCoverImageUrl = response_arr.productCoverImageUrl;
                let productPrice = response_arr.productPrice;
                let productPriceCurrency = response_arr.productPriceCurrency;
                let productAvailableStockTxt = response_arr.productAvailableStockTxt;
                let productVariationOptionsTxt = response_arr.productVariationOptionsTxt;
                let productPropertiesTxt = response_arr.productPropertiesTxt;
                let productDescription = response_arr.productDescription;
                let productEAN = response_arr.productEAN;
                let productUrl = response_arr.productUrl;
                let productUrlLinkText = response_arr.productUrlLinkText;
                let modalBodyHtml = "";
                modalBodyHtml += '<div class="row">';
                modalBodyHtml += '<div class="col-12 col-lg-6">';
                modalBodyHtml += '<img src="' + productCoverImageUrl + '" alt="Bild" class="img-fluid">';
                modalBodyHtml += '</div>';
                modalBodyHtml += '<div class="col-12 col-lg-6">';
                modalBodyHtml += '<h3>' + productName + '</h3>';
                modalBodyHtml += '<p class="h4">' + productPrice + ' ' + productPriceCurrency;
                if (parentThis.showStarBehindPrices) {
                    modalBodyHtml += '*';
                }
                modalBodyHtml += '</p>';

                modalBodyHtml += '<p>' + productAvailableStockTxt + '</p>';
                if (response_arr.productVariationOptions.length > 0) {
                    modalBodyHtml += '<p>' + productVariationOptionsTxt + '</p>';
                }
                if (response_arr.productProperties.length > 0) {
                    modalBodyHtml += '<p>' + productPropertiesTxt + '</p>';
                }
                modalBodyHtml += '<p>' + productDescription + '</p>';
                modalBodyHtml += '<p class="ean-text">' + productEAN + '</p>';
                modalBodyHtml += '<a href="' + productUrl + '" target="_blank">' + productUrlLinkText + '</a>';
                modalBodyHtml += '</div>';
                modalBodyHtml += '</div>';
                parentThis.showInfoModalWithMessage(modalBodyHtml);
            }
        });
        /* END - POST DATA */
    }

    bindDeleteBtns() {
        let parentThis = this;
        Array.from(document.querySelectorAll(".eseom-order-form-action-btn-delete")).forEach(thisElem => {
            thisElem.removeEventListener('click', parentThis.boundDeleteBtnEventHandler);
            thisElem.addEventListener('click', parentThis.boundDeleteBtnEventHandler);
        });
    }

    onDeleteBtnClicked(event) {
        let parentThis = this;
        let thisObj = event.currentTarget;
        let productRowsToUpdateObjects = [];
        let productRowsToUpdateSkus = [];
// falls remove: Neue Nummerierung notwendig
        thisObj.parentElement.parentElement.remove();
        let newCount = 1;

        Array.from(document.querySelectorAll("tr.eseom-order-form-table-tbody-tr")).forEach(thisElem2 => {
            thisElem2.querySelector('th').innerHTML = newCount;
            newCount++;
            let productNumber = thisElem2.querySelector('.eseom-order-form-sku-input').value;
            if (productNumber !== "" && !productRowsToUpdateSkus.includes(productNumber) && parseInt(thisElem2.querySelector('.eseom-order-form-quantity-input').value) > 0) {
                productRowsToUpdateSkus.push(productNumber);
                productRowsToUpdateObjects.push(thisElem2.querySelector('.eseom-order-form-sku-input'));
            }
        });

        parentThis.bindElements();

        for (let j = 0; j < productRowsToUpdateObjects.length; j++) {
            parentThis.showSearchSuggestions = false;
            parentThis.getPrice(productRowsToUpdateObjects[j]);
        }
        parentThis.downloadStatus();
    }

    /* Close product-search */
    bindCloseSearchBtn() {
        let parentThis = this;

        document.querySelector('#eseom-order-form-found-products-button-close').removeEventListener('click', parentThis.boundCloseFoundProductsBtnEventHandler);
        document.querySelector('#eseom-order-form-found-products-button-close').addEventListener('click', parentThis.boundCloseFoundProductsBtnEventHandler);
    }

    onCloseFoundProductsBtnClicked(event) {
        let parentThis = this;
        parentThis.hideFoundProductsDiv();
    }

    bindAddFoundProduct() {
        let parentThis = this;
        Array.from(document.querySelectorAll(".eseom-order-form-found-products-product")).forEach(thisElem => {
            thisElem.removeEventListener('click', parentThis.boundFoundProductBtnEventHandler);
            thisElem.addEventListener('click', parentThis.boundFoundProductBtnEventHandler);
        });
    }

    onFoundProductClicked(event) {
        let parentThis = this;
        let thisObj = event.currentTarget;
        let productNumberText = document.querySelector('#foundProductsDiv').getAttribute('data-productNumberText');
        let productNumber = thisObj.querySelector('.eseom-order-form-found-products-product-number').innerHTML;
        productNumber = productNumber.replace(productNumberText, '');
        productNumber = productNumber.replace(' ', '');

        let targetRow = document.querySelector('#eseom-order-form-found-products-corresponding-input').innerHTML;

        Array.from(document.querySelectorAll("#eseom-order-form-table tbody tr")).forEach(thisElem2 => {
            if (thisElem2.querySelector('.eseom-order-form-number').innerHTML == targetRow) {
                if (thisElem2.querySelector('.eseom-order-form-sku-input').value !== productNumber) {
                    thisElem2.querySelector('.eseom-order-form-sku-input').value = productNumber;
                    parentThis.showSearchSuggestions = false;
                    parentThis.getPrice(thisElem2.querySelector('.eseom-order-form-sku-input'));
                } else {
                    parentThis.hideFoundProductsDiv();
                }
            }
        });
        parentThis.hideFoundProductsDiv();
    }

    hideFoundProductsDiv() {
        document.querySelector('#foundProductsDiv').classList.remove('show');
        setTimeout(function () {
            document.querySelector('#foundProductsDiv').classList.add('d-none');
        }, 300);
    }

    showFoundProductsDiv() {
        document.querySelector('#foundProductsDiv').classList.add('show');
        document.querySelector('#foundProductsDiv').classList.remove('d-none');
    }

    getPrice(obj) {
        let parentThis = this;
        let productNumber = obj.parentElement.parentElement.querySelector('.eseom-order-form-sku-input').value;
        let qtySum = 0;

        let coverTdObj = obj.parentElement.parentElement.querySelector('.eseom-order-form-product-cover-td');
        let stockTdObj = obj.parentElement.parentElement.querySelector('.eseom-order-form-stock');
        let deliveryTimeTdObj = obj.parentElement.parentElement.querySelector('.eseom-order-form-delivery-time');
        let priceTdObj = obj.parentElement.parentElement.querySelector('.eseom-order-form-price');
        let totalPriceTdObj = obj.parentElement.parentElement.querySelector('.eseom-order-form-total-price');
        let totalPriceSumTdSpanValueObj = document.querySelector('#eseom-order-form-table .eseom-order-form-total-price-sum-value');
        let totalPriceSumTdSpanCurrencyObj = document.querySelector('#eseom-order-form-table .eseom-order-form-total-price-sum-currency');
        let productQuantityIp = obj.parentElement.parentElement.querySelector('.eseom-order-form-quantity-input');
        let productQuantity = productQuantityIp.value;
        let productNameTd = obj.parentElement.parentElement.querySelector('.eseom-order-form-product-name-td');

        let loadingSpinnerHtml = '<div class="spinner-border" style="width: 1rem; height: 1rem;" role="status"></div>';

        if (productNumber.length < this.minSearchLength) {
//        if (productNumber === "") {
            coverTdObj.innerHTML = "-";
            stockTdObj.innerHTML = "-";
            deliveryTimeTdObj.innerHTML = "-";
            priceTdObj.innerHTML = "-";
            totalPriceTdObj.innerHTML = "-";
            totalPriceTdObj.setAttribute("data-productTotalPrice", "0");
            totalPriceSumTdSpanValueObj.innerHTML = (Math.round(parentThis.getTotalPriceSum() * 100) / 100).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            productNameTd.innerHTML = "-";

            if (obj.classList.contains('eseom-order-form-sku-input')) {
                if (!obj.getAttribute('data-current-sku') || obj.getAttribute('data-current-sku') === undefined || obj.getAttribute('data-current-sku') === "") {
                    parentThis.hideFoundProductsDiv();
                    return;
                }
            } else {
                return;
            }
        }

        if (productQuantity === "") {
            productQuantity = 1;
            obj.parentElement.parentElement.querySelector('.eseom-order-form-quantity-input').value = productQuantity;
        }

        /* BEGIN - IF THE SKU WAS CHANGED -> FETCH THE PRICES OF THE SKU THAT WAS ENTERED BEFORE */
        if (obj.classList.contains('eseom-order-form-sku-input')) {
            if (obj.getAttribute('data-current-sku') && obj.getAttribute('data-current-sku') !== undefined && obj.getAttribute('data-current-sku') !== "") {
                let skuBeforeChange = obj.getAttribute('data-current-sku');
                obj.setAttribute('data-current-sku', '');

                Array.from(document.querySelectorAll(".eseom-order-form-sku-input")).forEach(thisElem => {
                    if (thisElem.getAttribute('data-current-sku') === skuBeforeChange && skuBeforeChange !== obj.value) {
                        parentThis.showSearchSuggestions = false;
                        parentThis.getPrice(thisElem);
                        return false;
                    }
                });
            }
        }
        /* END - IF THE SKU WAS CHANGED -> FETCH THE PRICES OF THE SKU THAT WAS ENTERED BEFORE */

        /* BEGIN - IF THE SKU INPUT WAS CLEARED AND THERE WERE MULTIPLE ENTRIES OF ONE SKU IN DIFFEERENT COLUMNS -> DO NOT REFRESH EMPTY ENTRIES IN THE TABLE */
        if (productNumber === "") {
            coverTdObj.innerHTML = "-";
            stockTdObj.innerHTML = "-";
            deliveryTimeTdObj.innerHTML = "-";
            priceTdObj.innerHTML = "-";
            totalPriceTdObj.innerHTML = "-";
            totalPriceTdObj.setAttribute("data-productTotalPrice", "0");
            totalPriceSumTdSpanValueObj.innerHTML = (Math.round(parentThis.getTotalPriceSum() * 100) / 100).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            productNameTd.innerHTML = "-";

            /* Call to set status of export-button */
            parentThis.downloadStatus();
            return;
        }
        /* END - IF THE SKU INPUT WAS CLEARED AND THERE WERE MULTIPLE ENTRIES OF ONE SKU IN DIFFEERENT COLUMNS -> DO NOT REFRESH EMPTY ENTRIES IN THE TABLE */

        /* BEGIN - SUM ALL QUANTITIES OF ENTRIES IN THE TABLE THAT HAVE THE SAME PRODUCTNUMBER AND SHOW A LOADING ICON ON EACH ENTRY WITH THE SAME SKU */
        Array.from(document.querySelectorAll(".eseom-order-form-sku-input")).forEach(thisElem2 => {
            if (thisElem2.value === productNumber && productNumber !== "") {
                if (thisElem2.parentElement.parentElement.querySelector('.eseom-order-form-quantity-input').value !== "") {
                    qtySum += parseInt(thisElem2.parentElement.parentElement.querySelector('.eseom-order-form-quantity-input').value);

                    thisElem2.parentElement.parentElement.querySelector('.eseom-order-form-stock').innerHTML = loadingSpinnerHtml;
                    thisElem2.parentElement.parentElement.querySelector('.eseom-order-form-delivery-time').innerHTML = loadingSpinnerHtml;
                    thisElem2.parentElement.parentElement.querySelector('.eseom-order-form-price').innerHTML = loadingSpinnerHtml;
                    thisElem2.parentElement.parentElement.querySelector('.eseom-order-form-total-price').innerHTML = loadingSpinnerHtml;
                    thisElem2.parentElement.parentElement.querySelector('.eseom-order-form-product-name-td').innerHTML = loadingSpinnerHtml;
                    thisElem2.parentElement.parentElement.querySelector('.eseom-order-form-product-cover-td').innerHTML = loadingSpinnerHtml;
                }
            }
        });
        /* END - SUM ALL QUANTITIES OF ENTRIES IN THE TABLE THAT HAVE THE SAME PRODUCTNUMBER AND SHOW A LOADING ICON ON EACH ENTRY WITH THE SAME SKU */

        totalPriceSumTdSpanValueObj.innerHTML = loadingSpinnerHtml;

        if (parentThis.currentSkuInputChangedElem === obj) {
            parentThis.showSearchSuggestions = true;
            parentThis.currentSkuInputChangedElem = null;
        }

        /* BEGIN - POST DATA */
        let ajaxPath = document.querySelector('#eseom-order-form-get-product-price-ajax-path').value;
        let dataToObject = {};
        dataToObject.productNumber = productNumber;
        dataToObject.productQuantity = qtySum;
        dataToObject.showSearchSuggestions = parentThis.showSearchSuggestions;
        dataToObject.navigationId = document.querySelector('#foundProductsDiv').getAttribute("data-navigationId");
        let stringifiedData = JSON.stringify(dataToObject);
        parentThis.client.post(ajaxPath, stringifiedData, (responseData) => {
            var response_arr = JSON.parse(responseData);
            if (parseInt(response_arr.success) !== 1 || (response_arr.foundProduct !== undefined && response_arr.foundProduct.productSku === undefined)) {
                Array.from(document.querySelectorAll(".eseom-order-form-sku-input")).forEach(thisElem3 => {
                    if (thisElem3.value === productNumber) {
                        if (response_arr.foundProduct.productAvailableStock !== undefined) {
                            thisElem3.parentElement.parentElement.querySelector('.eseom-order-form-stock').innerHTML = response_arr.foundProduct.productAvailableStock;

                            if (thisElem3.value !== "") {
                                if (response_arr.foundProduct.productErrorTxt !== undefined) {
                                    let stockHintHtml = "";
                                    stockHintHtml += '<span class="eseom-order-form-stock-alert-danger">';
                                    stockHintHtml += '<span class="eseom-order-form-stock-alert-danger-available-stock">';
                                    stockHintHtml += response_arr.foundProduct.productAvailableStock;
                                    stockHintHtml += '</span>';
                                    if (response_arr.foundProduct.productErrorTxt !== undefined) {
                                        stockHintHtml += '<span class="eseom-order-form-stock-alert-danger-stock-in-cart">';
                                        stockHintHtml += '<small>' + response_arr.foundProduct.productErrorTxt + '</small>';
                                        stockHintHtml += '</span>';
                                    }
                                    stockHintHtml += '</span>';
                                    thisElem3.parentElement.parentElement.querySelector('.eseom-order-form-stock').innerHTML = stockHintHtml;
                                }
                            }
                        } else {
                            thisElem3.parentElement.parentElement.querySelector('.eseom-order-form-stock').innerHTML = "-";
                        }

                        thisElem3.parentElement.parentElement.querySelector('.eseom-order-form-product-cover-td').innerHTML = "-";
                        thisElem3.parentElement.parentElement.querySelector('.eseom-order-form-delivery-time').innerHTML = "-";
                        thisElem3.parentElement.parentElement.querySelector('.eseom-order-form-price').innerHTML = "-";
                        thisElem3.parentElement.parentElement.querySelector('.eseom-order-form-total-price').innerHTML = "-";
                        thisElem3.parentElement.parentElement.querySelector('.eseom-order-form-total-price').setAttribute("data-productTotalPrice", "0");
                        if (response_arr.foundProduct.productName === undefined || response_arr.foundProduct.productName === "") {
                            thisElem3.parentElement.parentElement.querySelector('.eseom-order-form-product-name-td').innerHTML = "-";
                        } else {
                            if (response_arr.foundProduct.isMainProductWithVariants !== undefined && parseInt(response_arr.foundProduct.isMainProductWithVariants) === 1) {
                                /* BEGIN - IF THE ENTERED SKU IS A MAIN PRODUCT WITH VARIANTS -> SHOW A BUTTON SO A MODAL CAN BE OPENED WHERE ONE CAN CHOOSE A VARIANT */
                                thisElem3.parentElement.parentElement.querySelector('.eseom-order-form-sku-input').value = response_arr.foundProduct.productSku;
                                let chooseVariantBtnHtml = '<span class="eseom-order-form-main-product-name">' + response_arr.foundProduct.productName + '</span><button data-product-number="' + response_arr.foundProduct.productSku + '" type="button" class="eseom-order-form-choose-variant-btn btn btn-info"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="#758CA3" fill-rule="evenodd" d="M10.0944 16.3199 4.707 21.707c-.3905.3905-1.0237.3905-1.4142 0-.3905-.3905-.3905-1.0237 0-1.4142L8.68 14.9056C7.6271 13.551 7 11.8487 7 10c0-4.4183 3.5817-8 8-8s8 3.5817 8 8-3.5817 8-8 8c-1.8487 0-3.551-.627-4.9056-1.6801zM15 16c3.3137 0 6-2.6863 6-6s-2.6863-6-6-6-6 2.6863-6 6 2.6863 6 6 6z"></path></svg></button>';
                                thisElem3.parentElement.parentElement.querySelector('.eseom-order-form-product-name-td').innerHTML = chooseVariantBtnHtml;

                                Array.from(document.querySelectorAll(".eseom-order-form-product-name-td .eseom-order-form-choose-variant-btn")).forEach(thisElem4 => {
                                    thisElem4.removeEventListener('click', parentThis.boundChooseVariantBtnEventHandler);
                                    thisElem4.addEventListener('click', parentThis.boundChooseVariantBtnEventHandler);
                                });
                                /* END - IF THE ENTERED SKU IS A MAIN PRODUCT WITH VARIANTS -> SHOW A BUTTON SO A MODAL CAN BE OPENED WHERE ONE CAN CHOOSE A VARIANT */
                            } else {
                                let nameAndEAN = response_arr.foundProduct.productName + '<br><span class="ean-text">' + response_arr.foundProduct.productEAN + '</span>';
                                thisElem3.parentElement.parentElement.querySelector('.eseom-order-form-product-name-td').innerHTML = nameAndEAN;
                            }
                        }
                        totalPriceSumTdSpanValueObj.innerHTML = (Math.round(parentThis.getTotalPriceSum() * 100) / 100).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    }
                });
            } else {
                Array.from(document.querySelectorAll(".eseom-order-form-sku-input")).forEach(thisElem5 => {
                    if (thisElem5.value === productNumber) {
                        let showStarBehindPricesHtml = "";
                        if (parentThis.showStarBehindPrices) {
                            showStarBehindPricesHtml += '*';
                        }

                        let qtyInputElem = thisElem5.parentElement.parentElement.querySelector('.eseom-order-form-quantity-input');
                        thisElem5.parentElement.parentElement.querySelector('.eseom-order-form-sku-input').value = response_arr.foundProduct.productSku;
                        thisElem5.setAttribute('data-current-sku', response_arr.foundProduct.productSku);
                        thisElem5.parentElement.parentElement.querySelector('.eseom-order-form-stock').innerHTML = response_arr.foundProduct.productAvailableStock;
                        thisElem5.parentElement.parentElement.querySelector('.eseom-order-form-delivery-time').innerHTML = response_arr.foundProduct.productDeliveryTxt;
                        thisElem5.parentElement.parentElement.querySelector('.eseom-order-form-price').innerHTML = response_arr.foundProduct.productPriceFormatted + " " + response_arr.foundProduct.productPriceCurrency + showStarBehindPricesHtml;
                        thisElem5.parentElement.parentElement.querySelector('.eseom-order-form-total-price').innerHTML = (Math.round(response_arr.foundProduct.productPrice * parseInt(qtyInputElem.value) * 100) / 100).toLocaleString(undefined, {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }) + " " + response_arr.foundProduct.productPriceCurrency + showStarBehindPricesHtml;
                        thisElem5.parentElement.parentElement.querySelector('.eseom-order-form-total-price').setAttribute("data-productTotalPrice", response_arr.foundProduct.productPrice * parseInt(qtyInputElem.value));
                        thisElem5.parentElement.parentElement.querySelector('.eseom-order-form-product-cover-td').innerHTML = '<img src="' + response_arr.foundProduct.productCoverImageUrl + '" alt="Produktbild" class="img-fluid">';
                        let nameAndEAN = response_arr.foundProduct.productName + '<br><span class="ean-text">' + response_arr.foundProduct.productEAN + '</span>';
                        thisElem5.parentElement.parentElement.querySelector('.eseom-order-form-product-name-td').innerHTML = nameAndEAN;
                        totalPriceSumTdSpanValueObj.innerHTML = (Math.round(parentThis.getTotalPriceSum() * 100) / 100).toLocaleString(undefined, {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });

                        totalPriceSumTdSpanCurrencyObj.innerHTML = response_arr.foundProduct.productPriceCurrency + showStarBehindPricesHtml;
                    }
                });
            }

            if (document.activeElement === obj && (response_arr.foundProductSuggestions === undefined || (response_arr.foundProductSuggestions !== undefined && response_arr.foundProductSuggestions.length === 0) || (response_arr.foundProduct !== undefined && response_arr.foundProduct.productSku !== undefined && response_arr.foundProductSuggestions.length === 1))) {
                parentThis.hideFoundProductsDiv();
            }

            if (response_arr.foundProductSuggestions !== undefined && response_arr.foundProductSuggestions.length && response_arr.foundProductSuggestions.length > 1 || (response_arr.foundProduct !== undefined && response_arr.foundProduct.productSku === undefined && response_arr.foundProductSuggestions.length > 0)) {
                let foundProductsDiv = document.querySelector('#foundProductsDiv');
                let targetRow = obj.parentElement.parentElement.querySelector('.eseom-order-form-number').innerHTML;
                let foundProducts = '<div class="eseom-order-form-found-products-header">';
                foundProducts += '<div class="eseom-order-form-found-products-header-search">' + foundProductsDiv.getAttribute('data-productSearchText') + '</div>';
                foundProducts += '<button type="button" id="eseom-order-form-found-products-button-close" class="btn btn-close"></button>';
                foundProducts += '<div id="eseom-order-form-found-products-corresponding-input">' + targetRow + '</div>';
                foundProducts += '</div>';

                response_arr.foundProductSuggestions.forEach(function (item, index, arr) {
                    foundProducts += '<div class="eseom-order-form-found-products-product">';
                    foundProducts += '<div class="eseom-order-form-found-products-product-img"><img src="' + item.productCoverImageUrl + '" alt="Bild" class="img-fluid"></div>';
                    foundProducts += '<div class="eseom-order-form-found-products-product-name-number-wrapper">';
                    foundProducts += '<div class="eseom-order-form-found-products-product-name">' + response_arr.foundProductSuggestions[index].name + '</div>';
                    if (response_arr.foundProductSuggestions[index].ean !== "") {
                        foundProducts += '<div class="eseom-order-form-found-products-product-ean ean-text">' + response_arr.foundProductSuggestions[index].ean + '</div>';
                    }
                    foundProducts += '<div class="eseom-order-form-found-products-product-number">' + document.querySelector('#foundProductsDiv').getAttribute('data-productNumberText') + ' ' + item.number + '</div>';

                    if (item.productVariationOptions.length > 0) {
                        foundProducts += '<div class="eseom-order-form-found-products-product-options">' + item.productVariationOptionsTxt + '</div>';
                    }
                    foundProducts += '</div>';
                    foundProducts += '</div>';
                });

                foundProductsDiv.innerHTML = foundProducts;

                let inputElemBoundingClientRect = obj.getBoundingClientRect();
                foundProductsDiv.style.left = inputElemBoundingClientRect.left + window.scrollX + 'px';
                foundProductsDiv.style.top = inputElemBoundingClientRect.top + inputElemBoundingClientRect.height + window.scrollY + 'px';
                parentThis.bindCloseSearchBtn();
                parentThis.bindAddFoundProduct();

                parentThis.showFoundProductsDiv();
            }

            /* Call to set status of export-button */
            parentThis.downloadStatus();
        });
        /* END - POST DATA */
    }

    onChooseVariantBtnClicked(event) {
        let parentThis = this;
        let eventObj = event.currentTarget;
        parentThis.loadingModalInstance.show();

        /* BEGIN - POST DATA */
        let ajaxPath = document.querySelector('#eseom-order-form-get-product-variations-ajax-path').value;
        let dataToObject = {};
        dataToObject.productNumber = eventObj.getAttribute("data-product-number");
//                                    dataToObject.productNumber = productNumber;
        let stringifiedData = JSON.stringify(dataToObject);
        parentThis.client.post(ajaxPath, stringifiedData, (responseData) => {
            var response_arr = JSON.parse(responseData);

            if (parseInt(response_arr.success) !== 1) {
                parentThis.showErrorModalWithMessage(response_arr.msg);
            } else {
                parentThis.showChooseVariantModalWithMessage(response_arr.variationHtmlTable, eventObj.parentElement.parentElement.querySelector('.eseom-order-form-sku-input'));
            }
        });
        /* END - POST DATA */
    }

    showOffcanvasCart() {
        let parentThis = this;
        parentThis.loadingModalInstance.hide();
        let clickEvent = new Event('click');
        document.getElementsByClassName("header-cart")[0].dispatchEvent(clickEvent);
        let touchstartEvent = new Event('touchstart');
        document.getElementsByClassName("header-cart")[0].dispatchEvent(touchstartEvent);
        parentThis.refreshAllPricesInTable();
    }

    showCart() {
        window.location.href = '/checkout/cart';
    }

    showConfirm() {
        window.location.href = '/checkout/confirm';
    }

    refreshAllPricesInTable() {
        let parentThis = this;
        let fetchedProductNumbersArr = [];
        /* BEGIN - FETCH NEW PRICES OF EACH ENTRY IN THE TABLE AS THERE ARE PRODUCTS ADDED TO THE CART AND THE PRICES COULD CHANGE DUE TO LIST PRICE AND THE QUANTITY */
        Array.from(document.querySelectorAll(".eseom-order-form-quantity-input")).forEach(thisElem => {
            let sku = thisElem.parentElement.parentElement.querySelector('.eseom-order-form-sku-input').value;
            if (sku !== "" && !fetchedProductNumbersArr.includes(sku) && thisElem.value !== "" && parseInt(thisElem.value) > 0) {
                fetchedProductNumbersArr.push(sku);
                parentThis.showSearchSuggestions = false;
                parentThis.getPrice(thisElem);
            }
        });
        /* END - FETCH NEW PRICES OF EACH ENTRY IN THE TABLE AS THERE ARE PRODUCTS ADDED TO THE CART AND THE PRICES COULD CHANGE DUE TO LIST PRICE AND THE QUANTITY */
    }

    addNewPosition(number, quantity) {
        let parentThis = this;
        let countPos = document.querySelectorAll('.eseom-order-form-table-tbody-tr').length + 1;
        let placeholderSku = document.querySelector('#eseom-order-form-table').getAttribute('data-skuPlaceholder');
        let placeholderQuantity = document.querySelector('#eseom-order-form-table').getAttribute('data-quantityPlaceholder');
        let titleDelete = document.querySelector('#eseom-order-form-table').getAttribute('data-titleDelete');
        let newRow = "";
        newRow += '<tbody>';
        newRow += '<tr class="eseom-order-form-table-tbody-tr">';
        newRow += '<th class="eseom-order-form-number align-middle" scope="row">' + countPos + '</th>';
        newRow += '<td class="eseom-order-form-product-cover-td align-middle">-</td>';
        newRow += '<td class="align-middle"><input class="eseom-order-form-sku-input form-control" type="text"  value="' + number + '"placeholder="' + placeholderSku + '"></td>';
        newRow += '<td class="eseom-order-form-product-name-td align-middle">-</td>';
        newRow += '<td class="align-middle"><input class="eseom-order-form-quantity-input form-control" type="number" value="' + quantity + '" min="1" step="1" placeholder="' + placeholderQuantity + '"></td>';
        newRow += '<td class="eseom-order-form-stock align-middle">-</td>';
        newRow += '<td class="eseom-order-form-delivery-time align-middle">-</td>';
        newRow += '<td class="eseom-order-form-price align-middle">-</td>';
        newRow += '<td class="eseom-order-form-total-price align-middle">-</td>';
        newRow += '<td class="eseom-order-form-information align-middle">';
        newRow += '<button class="eseom-order-form-action-btn-info btn btn-info" type="button" aria-label="info button">';
        newRow += '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="#fff" fill-rule="evenodd" d="M12,7 C12.5522847,7 13,7.44771525 13,8 C13,8.55228475 12.5522847,9 12,9 C11.4477153,9 11,8.55228475 11,8 C11,7.44771525 11.4477153,7 12,7 Z M13,16 C13,16.5522847 12.5522847,17 12,17 C11.4477153,17 11,16.5522847 11,16 L11,11 C11,10.4477153 11.4477153,10 12,10 C12.5522847,10 13,10.4477153 13,11 L13,16 Z M24,12 C24,18.627417 18.627417,24 12,24 C5.372583,24 6.14069502e-15,18.627417 5.32907052e-15,12 C-8.11624501e-16,5.372583 5.372583,4.77015075e-15 12,3.55271368e-15 C18.627417,5.58919772e-16 24,5.372583 24,12 Z M12,2 C6.4771525,2 2,6.4771525 2,12 C2,17.5228475 6.4771525,22 12,22 C17.5228475,22 22,17.5228475 22,12 C22,6.4771525 17.5228475,2 12,2 Z"></path></svg>';
        newRow += '</button>';
        newRow += '</td>';
        newRow += '<td class="eseom-order-form-delete align-middle">';
        newRow += '<button class="eseom-order-form-action-btn-delete btn btn-delete" type="button" aria-label="delete button" title="' + titleDelete + '">';
        newRow += '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 18 18"><path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"></path><path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4L4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"></path></svg>';
        newRow += '</button>';
        newRow += '</td>';
        newRow += '</tr>';
        newRow += '</tbody>';
        var tempTable = document.createElement('table');
        tempTable.innerHTML = newRow.trim();
        let newRowElem = tempTable.firstChild.firstChild;
        document.querySelector('#eseom-order-form-table tbody').append(newRowElem);

        return newRowElem;
    }

    showSuccessModalWithMessage(successMsg) {
        let parentThis = this;
        parentThis.loadingModalInstance.hide();
        document.querySelector('#eseom-order-form-success-modal .modal-body').innerHTML = successMsg;
        parentThis.successModalInstance.show();
    }

    showInfoModalWithMessage(infoMsg) {
        let parentThis = this;
        parentThis.loadingModalInstance.hide();
        document.querySelector('#eseom-order-form-info-modal .modal-body').innerHTML = infoMsg;
        parentThis.infoModalInstance.show();
    }

    showErrorModalWithMessage(errorMsg) {
        let parentThis = this;
        parentThis.loadingModalInstance.hide();
        document.querySelector('#eseom-order-form-error-modal .modal-body').innerHTML = errorMsg;
        parentThis.errorModalInstance.show();
    }

    showChooseVariantModalWithMessage(msg, colSkuObj) {
        let parentThis = this;
        parentThis.loadingModalInstance.hide();
        document.querySelector('#eseom-order-form-choose-variant-modal .modal-body').innerHTML = msg;
        parentThis.chooseVariantModalInstance.show();

        Array.from(document.querySelectorAll(".eseom-order-form-choose-variant-use-btn")).forEach(thisElem => {
            thisElem.addEventListener('click', (e) => {
                colSkuObj.value = e.currentTarget.getAttribute('data-variant-order-number');
                parentThis.chooseVariantModalInstance.hide();
                parentThis.handleSkuInputChange(colSkuObj);
            });
        });
    }
}

