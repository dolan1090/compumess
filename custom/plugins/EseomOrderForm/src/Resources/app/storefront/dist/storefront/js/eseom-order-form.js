"use strict";(self.webpackChunk=self.webpackChunk||[]).push([["eseom-order-form"],{2791:(e,t,r)=>{var o=r(6285),n=r(8254),d=r(1966);class a extends o.Z{init(){this.editedSkuInputArr=[],this.editedQtyInputArr=[],this.boundSkuInputEventHandler,this.boundQtyInputEventHandler,this.boundInfoBtnEventHandler,this.boundDeleteBtnEventHandler,this.boundCloseFoundProductsBtnEventHandler,this.boundFoundProductBtnEventHandler,this.boundChooseVariantBtnEventHandler,this.client=new n.Z,this.offcanvasCartActionBlocked=!0,this.showStarBehindPrices=!1,this.minSearchLength=3,this.showSearchSuggestions=!0,this.currentSkuInputChangedElem,this.productSuggestionClicked=!1;let e=this;const t=document.getElementById("eseom-order-form-loading-modal");e.loadingModalInstance=new bootstrap.Modal(t,{show:!0,backdrop:"static",keyboard:!1});const r=document.getElementById("eseom-order-form-choose-variant-modal");e.chooseVariantModalInstance=new bootstrap.Modal(r,{show:!0,backdrop:"static",keyboard:!1});const o=document.getElementById("eseom-order-form-info-modal");e.infoModalInstance=new bootstrap.Modal(o,{show:!0,backdrop:"static",keyboard:!1});const d=document.getElementById("eseom-order-form-error-modal");e.errorModalInstance=new bootstrap.Modal(d,{show:!0,backdrop:"static",keyboard:!1});const a=document.getElementById("eseom-order-form-success-modal");e.successModalInstance=new bootstrap.Modal(a,{show:!0,backdrop:"static",keyboard:!1}),document.querySelector("#eseom-order-form-add-to-cart-btn").addEventListener("click",this.addToCart.bind(this)),document.querySelector("#eseom-order-form-add-position").addEventListener("click",(()=>{e.addNewPosition("",1),e.bindElements()})),e.boundSkuInputEventHandler=e.onSkuInputChange.bind(e),e.boundQtyInputEventHandler=e.onQuantityInputChange.bind(e),e.boundInfoBtnEventHandler=e.onInfoBtnClicked.bind(e),e.boundDeleteBtnEventHandler=e.onDeleteBtnClicked.bind(e),e.boundCloseFoundProductsBtnEventHandler=e.onCloseFoundProductsBtnClicked.bind(e),e.boundFoundProductBtnEventHandler=e.onFoundProductClicked.bind(e),e.boundChooseVariantBtnEventHandler=e.onChooseVariantBtnClicked.bind(e),e.bindElements(),document.querySelector("#eseom-order-form-show-star-behind-prices")&&(e.showStarBehindPrices=!0),document.querySelector("#eseom-order-form-clear-positions").addEventListener("click",(t=>{Array.from(document.querySelectorAll(".eseom-order-form-sku-input")).forEach((t=>{t.value="",e.handleSkuInputChange(t)})),e.downloadStatus()})),document.querySelector(".eseom-order-form-import").addEventListener("click",(e=>{document.querySelector(".eseom-order-form-import-file.custom-file-input").click()})),document.querySelector(".eseom-order-form-import-file.custom-file-input").addEventListener("change",e.importFile.bind(e)),document.querySelector(".eseom-order-form-export").addEventListener("click",e.exportFile.bind(e)),e.refreshAllPricesInTable(),document.querySelector('.header-cart[data-offcanvas-cart="true"]').addEventListener("click",(t=>{e.setOffcanvasCartActionBlocked(!0)}));window.PluginManager.getPluginInstanceFromElement(document.querySelector("[data-offcanvas-cart]"),"OffCanvasCart").$emitter.subscribe("registerEvents",this.onOffcanvasCartChanged)}importFile(e){let t=this,r=e.currentTarget,o=document.querySelector("#eseom-order-form-table > tbody"),n=r.value.length?r.value.replace(/C:\\fakepath\\/i,""):"";if(""!==n){document.querySelector(".eseom-order-form-import-label").innerHTML=n;let e=";";"comma"===document.querySelector("#eseom-order-form-table").getAttribute("data-delimiter")&&(e=",");var d=document.getElementById("upload-file");if(/^([a-zA-Z0-9\s_\\.\-:])+(.csv|.txt)$/.test(d.value.toLowerCase()))if("undefined"!=typeof FileReader){var a=new FileReader;a.onload=function(r){let n=r.target.result.split("\n");n[0]=n[0].replace(/\r/,"");let d=n[0].split(e),a=d.indexOf("ProductNr / EAN"),u=d.indexOf("Quantity"),s=[],l=[];if(-1===a||-1===u)alert("CSV-Header is not correct");else{for(;o.rows.item(o.rows.length-1)&&""===o.rows.item(o.rows.length-1).querySelector(".eseom-order-form-sku-input").value;)o.rows.item(o.rows.length-1).remove();for(let r=1;r<n.length;r++)if(n[r]=n[r].replace(/\r/,""),""!==n[r].split(e)[a]&&n[r].split(e)[u]>0){let o=n[r].split(e)[a],d=t.addNewPosition(o,n[r].split(e)[u]);l.includes(o)||(l.push(o),s.push(d.querySelector(".eseom-order-form-sku-input")))}t.bindElements();for(let e=0;e<s.length;e++)t.showSearchSuggestions=!1,t.getPrice(s[e])}},a.readAsText(d.files[0])}else alert("This browser does not support HTML5.");else alert("Please upload a valid CSV file.");document.querySelector(".eseom-order-form-import-label").innerHTML=document.querySelector(".eseom-order-form-import-label").getAttribute("data-label"),document.querySelector("#upload-file").value=""}else document.querySelector(".eseom-order-form-import-label").innerHTML=document.querySelector(".eseom-order-form-import-label").getAttribute("data-label")}bindElements(){let e=this;e.bindInputs(),e.bindInfoBtns(),e.bindDeleteBtns()}exportFile(e){let t=e.currentTarget;if(t.classList.contains("export-active")){const e=["ProductNr / EAN","ProductName","Quantity"];let r=";";"comma"===document.querySelector("#eseom-order-form-table").getAttribute("data-delimiter")&&(r=",");let o=e.join(r);Array.from(document.querySelectorAll("#eseom-order-form-table tbody tr")).forEach((e=>{let t=[];if(""!==e.querySelector(".eseom-order-form-sku-input").value&&"-"!==e.querySelector(".eseom-order-form-price").innerHTML){t.push(e.querySelector(".eseom-order-form-sku-input").value);let n=e.querySelector(".eseom-order-form-product-name-td").innerHTML.split("<br>")[0];t.push(n),t.push(e.querySelector(".eseom-order-form-quantity-input").value),o+="\n"+t.join(r)}}));const n=new Blob([o],{type:"text/csv"}),d=window.URL.createObjectURL(n),a=document.createElement("a");let u=new Date,s=u.getFullYear()+("0"+(u.getMonth()+1)).slice(-2)+("0"+u.getDate()).slice(-2)+"_"+u.getHours()+u.getMinutes()+u.getSeconds();a.setAttribute("href",d),a.setAttribute("download",t.getAttribute("data-filename")+"_"+s+".csv"),a.click()}}addToCart(e){let t=this,r=e.currentTarget,o=[];document.querySelector("#eseom-order-form-table tbody tr")&&Array.from(document.querySelectorAll("#eseom-order-form-table tbody tr")).forEach((e=>{if(""!==e.querySelector(".eseom-order-form-sku-input").value){let t={};t.productNr=e.querySelector(".eseom-order-form-sku-input").value,t.productQuantity=e.querySelector(".eseom-order-form-quantity-input").value,o.push(t)}}));let n=document.querySelector("#eseom-order-form-add-to-cart-ajax-path").value,a={};a.positions=o;let u=JSON.stringify(a);t.loadingModalInstance.show(),t.client.post(n,u,(e=>{var o=JSON.parse(e);if(1!==parseInt(o.success))t.showErrorModalWithMessage(o.msg);else if("openModal"===r.getAttribute("data-success-action")){t.showSuccessModalWithMessage(o.msg);const e=window.PluginManager.getPluginInstances("CartWidget");d.Z.iterate(e,(e=>e.fetch())),t.refreshAllPricesInTable()}else"openOffcanvasCart"===r.getAttribute("data-success-action")?t.showOffcanvasCart():"openCart"===r.getAttribute("data-success-action")?t.showCart():t.showConfirm()}))}downloadStatus(){let e=!1;Array.from(document.querySelectorAll("#eseom-order-form-table tbody tr")).forEach((t=>{"-"!==t.querySelector(".eseom-order-form-price").innerHTML&&(t.querySelector(".eseom-order-form-price > div")||(e=!0))})),e?document.querySelector(".eseom-order-form-export").classList.add("export-active"):document.querySelector(".eseom-order-form-export").classList.remove("export-active")}onOffcanvasCartChanged(){const e=window.PluginManager.getPluginInstanceFromElement(document.querySelector("[data-eseom-order-form]"),"EseomOrderForm");e.getOffcanvasCartActionBlocked()?e.setOffcanvasCartActionBlocked(!1):e.refreshAllPricesInTable()}setOffcanvasCartActionBlocked(e){this.offcanvasCartActionBlocked=e}getOffcanvasCartActionBlocked(){return this.offcanvasCartActionBlocked}bindInputs(){let e=this;Array.from(document.querySelectorAll(".eseom-order-form-sku-input")).forEach((t=>{t.removeEventListener("input",e.boundSkuInputEventHandler),t.addEventListener("input",e.boundSkuInputEventHandler)})),Array.from(document.querySelectorAll(".eseom-order-form-quantity-input")).forEach((t=>{t.removeEventListener("input",e.boundQtyInputEventHandler),t.addEventListener("input",e.boundQtyInputEventHandler)}))}onSkuInputChange(e){let t=e.currentTarget;this.handleSkuInputChange(t),this.currentSkuInputChangedElem=t}handleSkuInputChange(e){let t=this,r=[...e.parentElement.parentElement.parentElement.children].indexOf(e.parentElement.parentElement);null!==t.editedSkuInputArr[r]&&void 0!==t.editedSkuInputArr[r]&&(clearTimeout(t.editedSkuInputArr[r]),t.editedSkuInputArr[r]=null),t.editedSkuInputArr[r]=setTimeout((function(){e.value===e.getAttribute("data-current-sku")||t.productSuggestionClicked?t.showSearchSuggestions=!1:t.showSearchSuggestions=!0,t.productSuggestionClicked=!1,t.getPrice(e)}),750)}onQuantityInputChange(e){let t=this,r=e.currentTarget,o=r.parentElement.parentElement.querySelector(".eseom-order-form-sku-input"),n=[...r.parentElement.parentElement.parentElement.children].indexOf(r.parentElement.parentElement);null!==t.editedQtyInputArr[n]&&void 0!==t.editedQtyInputArr[n]&&(clearTimeout(t.editedQtyInputArr[n]),t.editedQtyInputArr[n]=null),t.editedQtyInputArr[n]=setTimeout((function(){t.showSearchSuggestions=!1,t.getPrice(o)}),750)}getTotalPriceSum(){let e=0;return Array.from(document.querySelectorAll("#eseom-order-form-table tbody tr .eseom-order-form-total-price")).forEach((t=>{"-"!==t.innerHTML&&void 0!==t.getAttribute("data-productTotalPrice")&&(e+=parseFloat(t.getAttribute("data-productTotalPrice")))})),e}bindInfoBtns(){let e=this;Array.from(document.querySelectorAll(".eseom-order-form-action-btn-info")).forEach((t=>{t.removeEventListener("click",e.boundInfoBtnEventHandler),t.addEventListener("click",e.boundInfoBtnEventHandler)}))}onInfoBtnClicked(e){let t=this,r=e.currentTarget;t.loadingModalInstance.show();let o=r.parentElement.parentElement.querySelector(".eseom-order-form-sku-input").value,n=r.parentElement.parentElement.querySelector(".eseom-order-form-quantity-input").value;""===n&&(n=1,r.parentElement.parentElement.querySelector(".eseom-order-form-quantity-input").value=n);let d=document.querySelector("#eseom-order-form-get-product-info-ajax-path").value,a={};a.productNumber=o,a.productQuantity=n,a.navigationId=document.querySelector("#foundProductsDiv").getAttribute("data-navigationId");let u=JSON.stringify(a);t.client.post(d,u,(e=>{var r=JSON.parse(e);if(1!==parseInt(r.success))t.showErrorModalWithMessage(r.msg);else{document.querySelector("#eseom-order-form-info-modal .modal-body").innerHTML="";let e=r.productName,o=r.productCoverImageUrl,n=r.productPrice,d=r.productPriceCurrency,a=r.productAvailableStockTxt,u=r.productVariationOptionsTxt,s=r.productPropertiesTxt,l=r.productDescription,i=r.productEAN,c=r.productUrl,m=r.productUrlLinkText,p="";p+='<div class="row">',p+='<div class="col-12 col-lg-6">',p+='<img src="'+o+'" alt="Bild" class="img-fluid">',p+="</div>",p+='<div class="col-12 col-lg-6">',p+="<h3>"+e+"</h3>",p+='<p class="h4">'+n+" "+d,t.showStarBehindPrices&&(p+="*"),p+="</p>",p+="<p>"+a+"</p>",r.productVariationOptions.length>0&&(p+="<p>"+u+"</p>"),r.productProperties.length>0&&(p+="<p>"+s+"</p>"),p+="<p>"+l+"</p>",p+='<p class="ean-text">'+i+"</p>",p+='<a href="'+c+'" target="_blank">'+m+"</a>",p+="</div>",p+="</div>",t.showInfoModalWithMessage(p)}}))}bindDeleteBtns(){let e=this;Array.from(document.querySelectorAll(".eseom-order-form-action-btn-delete")).forEach((t=>{t.removeEventListener("click",e.boundDeleteBtnEventHandler),t.addEventListener("click",e.boundDeleteBtnEventHandler)}))}onDeleteBtnClicked(e){let t=this,r=e.currentTarget,o=[],n=[];r.parentElement.parentElement.remove();let d=1;Array.from(document.querySelectorAll("tr.eseom-order-form-table-tbody-tr")).forEach((e=>{e.querySelector("th").innerHTML=d,d++;let t=e.querySelector(".eseom-order-form-sku-input").value;""!==t&&!n.includes(t)&&parseInt(e.querySelector(".eseom-order-form-quantity-input").value)>0&&(n.push(t),o.push(e.querySelector(".eseom-order-form-sku-input")))})),t.bindElements();for(let e=0;e<o.length;e++)t.showSearchSuggestions=!1,t.getPrice(o[e]);t.downloadStatus()}bindCloseSearchBtn(){document.querySelector("#eseom-order-form-found-products-button-close").removeEventListener("click",this.boundCloseFoundProductsBtnEventHandler),document.querySelector("#eseom-order-form-found-products-button-close").addEventListener("click",this.boundCloseFoundProductsBtnEventHandler)}onCloseFoundProductsBtnClicked(e){this.hideFoundProductsDiv()}bindAddFoundProduct(){let e=this;Array.from(document.querySelectorAll(".eseom-order-form-found-products-product")).forEach((t=>{t.removeEventListener("click",e.boundFoundProductBtnEventHandler),t.addEventListener("click",e.boundFoundProductBtnEventHandler)}))}onFoundProductClicked(e){let t=this,r=e.currentTarget,o=document.querySelector("#foundProductsDiv").getAttribute("data-productNumberText"),n=r.querySelector(".eseom-order-form-found-products-product-number").innerHTML;n=n.replace(o,""),n=n.replace(" ","");let d=document.querySelector("#eseom-order-form-found-products-corresponding-input").innerHTML;Array.from(document.querySelectorAll("#eseom-order-form-table tbody tr")).forEach((e=>{e.querySelector(".eseom-order-form-number").innerHTML==d&&(e.querySelector(".eseom-order-form-sku-input").value!==n?(e.querySelector(".eseom-order-form-sku-input").value=n,t.showSearchSuggestions=!1,t.getPrice(e.querySelector(".eseom-order-form-sku-input"))):t.hideFoundProductsDiv())})),t.hideFoundProductsDiv()}hideFoundProductsDiv(){document.querySelector("#foundProductsDiv").classList.remove("show"),setTimeout((function(){document.querySelector("#foundProductsDiv").classList.add("d-none")}),300)}showFoundProductsDiv(){document.querySelector("#foundProductsDiv").classList.add("show"),document.querySelector("#foundProductsDiv").classList.remove("d-none")}getPrice(e){let t=this,r=e.parentElement.parentElement.querySelector(".eseom-order-form-sku-input").value,o=0,n=e.parentElement.parentElement.querySelector(".eseom-order-form-product-cover-td"),d=e.parentElement.parentElement.querySelector(".eseom-order-form-stock"),a=e.parentElement.parentElement.querySelector(".eseom-order-form-delivery-time"),u=e.parentElement.parentElement.querySelector(".eseom-order-form-price"),s=e.parentElement.parentElement.querySelector(".eseom-order-form-total-price"),l=document.querySelector("#eseom-order-form-table .eseom-order-form-total-price-sum-value"),i=document.querySelector("#eseom-order-form-table .eseom-order-form-total-price-sum-currency"),c=e.parentElement.parentElement.querySelector(".eseom-order-form-quantity-input").value,m=e.parentElement.parentElement.querySelector(".eseom-order-form-product-name-td"),p='<div class="spinner-border" style="width: 1rem; height: 1rem;" role="status"></div>';if(r.length<this.minSearchLength){if(n.innerHTML="-",d.innerHTML="-",a.innerHTML="-",u.innerHTML="-",s.innerHTML="-",s.setAttribute("data-productTotalPrice","0"),l.innerHTML=(Math.round(100*t.getTotalPriceSum())/100).toLocaleString(void 0,{minimumFractionDigits:2,maximumFractionDigits:2}),m.innerHTML="-",!e.classList.contains("eseom-order-form-sku-input"))return;if(!e.getAttribute("data-current-sku")||void 0===e.getAttribute("data-current-sku")||""===e.getAttribute("data-current-sku"))return void t.hideFoundProductsDiv()}if(""===c&&(c=1,e.parentElement.parentElement.querySelector(".eseom-order-form-quantity-input").value=c),e.classList.contains("eseom-order-form-sku-input")&&e.getAttribute("data-current-sku")&&void 0!==e.getAttribute("data-current-sku")&&""!==e.getAttribute("data-current-sku")){let r=e.getAttribute("data-current-sku");e.setAttribute("data-current-sku",""),Array.from(document.querySelectorAll(".eseom-order-form-sku-input")).forEach((o=>{if(o.getAttribute("data-current-sku")===r&&r!==e.value)return t.showSearchSuggestions=!1,t.getPrice(o),!1}))}if(""===r)return n.innerHTML="-",d.innerHTML="-",a.innerHTML="-",u.innerHTML="-",s.innerHTML="-",s.setAttribute("data-productTotalPrice","0"),l.innerHTML=(Math.round(100*t.getTotalPriceSum())/100).toLocaleString(void 0,{minimumFractionDigits:2,maximumFractionDigits:2}),m.innerHTML="-",void t.downloadStatus();Array.from(document.querySelectorAll(".eseom-order-form-sku-input")).forEach((e=>{e.value===r&&""!==r&&""!==e.parentElement.parentElement.querySelector(".eseom-order-form-quantity-input").value&&(o+=parseInt(e.parentElement.parentElement.querySelector(".eseom-order-form-quantity-input").value),e.parentElement.parentElement.querySelector(".eseom-order-form-stock").innerHTML=p,e.parentElement.parentElement.querySelector(".eseom-order-form-delivery-time").innerHTML=p,e.parentElement.parentElement.querySelector(".eseom-order-form-price").innerHTML=p,e.parentElement.parentElement.querySelector(".eseom-order-form-total-price").innerHTML=p,e.parentElement.parentElement.querySelector(".eseom-order-form-product-name-td").innerHTML=p,e.parentElement.parentElement.querySelector(".eseom-order-form-product-cover-td").innerHTML=p)})),l.innerHTML=p,t.currentSkuInputChangedElem===e&&(t.showSearchSuggestions=!0,t.currentSkuInputChangedElem=null);let f=document.querySelector("#eseom-order-form-get-product-price-ajax-path").value,h={};h.productNumber=r,h.productQuantity=o,h.showSearchSuggestions=t.showSearchSuggestions,h.navigationId=document.querySelector("#foundProductsDiv").getAttribute("data-navigationId");let g=JSON.stringify(h);t.client.post(f,g,(o=>{var n=JSON.parse(o);if(1!==parseInt(n.success)||void 0!==n.foundProduct&&void 0===n.foundProduct.productSku?Array.from(document.querySelectorAll(".eseom-order-form-sku-input")).forEach((e=>{if(e.value===r){if(void 0!==n.foundProduct.productAvailableStock){if(e.parentElement.parentElement.querySelector(".eseom-order-form-stock").innerHTML=n.foundProduct.productAvailableStock,""!==e.value&&void 0!==n.foundProduct.productErrorTxt){let t="";t+='<span class="eseom-order-form-stock-alert-danger">',t+='<span class="eseom-order-form-stock-alert-danger-available-stock">',t+=n.foundProduct.productAvailableStock,t+="</span>",void 0!==n.foundProduct.productErrorTxt&&(t+='<span class="eseom-order-form-stock-alert-danger-stock-in-cart">',t+="<small>"+n.foundProduct.productErrorTxt+"</small>",t+="</span>"),t+="</span>",e.parentElement.parentElement.querySelector(".eseom-order-form-stock").innerHTML=t}}else e.parentElement.parentElement.querySelector(".eseom-order-form-stock").innerHTML="-";if(e.parentElement.parentElement.querySelector(".eseom-order-form-product-cover-td").innerHTML="-",e.parentElement.parentElement.querySelector(".eseom-order-form-delivery-time").innerHTML="-",e.parentElement.parentElement.querySelector(".eseom-order-form-price").innerHTML="-",e.parentElement.parentElement.querySelector(".eseom-order-form-total-price").innerHTML="-",e.parentElement.parentElement.querySelector(".eseom-order-form-total-price").setAttribute("data-productTotalPrice","0"),void 0===n.foundProduct.productName||""===n.foundProduct.productName)e.parentElement.parentElement.querySelector(".eseom-order-form-product-name-td").innerHTML="-";else if(void 0!==n.foundProduct.isMainProductWithVariants&&1===parseInt(n.foundProduct.isMainProductWithVariants)){e.parentElement.parentElement.querySelector(".eseom-order-form-sku-input").value=n.foundProduct.productSku;let r='<span class="eseom-order-form-main-product-name">'+n.foundProduct.productName+'</span><button data-product-number="'+n.foundProduct.productSku+'" type="button" class="eseom-order-form-choose-variant-btn btn btn-info"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="#758CA3" fill-rule="evenodd" d="M10.0944 16.3199 4.707 21.707c-.3905.3905-1.0237.3905-1.4142 0-.3905-.3905-.3905-1.0237 0-1.4142L8.68 14.9056C7.6271 13.551 7 11.8487 7 10c0-4.4183 3.5817-8 8-8s8 3.5817 8 8-3.5817 8-8 8c-1.8487 0-3.551-.627-4.9056-1.6801zM15 16c3.3137 0 6-2.6863 6-6s-2.6863-6-6-6-6 2.6863-6 6 2.6863 6 6 6z"></path></svg></button>';e.parentElement.parentElement.querySelector(".eseom-order-form-product-name-td").innerHTML=r,Array.from(document.querySelectorAll(".eseom-order-form-product-name-td .eseom-order-form-choose-variant-btn")).forEach((e=>{e.removeEventListener("click",t.boundChooseVariantBtnEventHandler),e.addEventListener("click",t.boundChooseVariantBtnEventHandler)}))}else{let t=n.foundProduct.productName+'<br><span class="ean-text">'+n.foundProduct.productEAN+"</span>";e.parentElement.parentElement.querySelector(".eseom-order-form-product-name-td").innerHTML=t}l.innerHTML=(Math.round(100*t.getTotalPriceSum())/100).toLocaleString(void 0,{minimumFractionDigits:2,maximumFractionDigits:2})}})):Array.from(document.querySelectorAll(".eseom-order-form-sku-input")).forEach((e=>{if(e.value===r){let r="";t.showStarBehindPrices&&(r+="*");let o=e.parentElement.parentElement.querySelector(".eseom-order-form-quantity-input");e.parentElement.parentElement.querySelector(".eseom-order-form-sku-input").value=n.foundProduct.productSku,e.setAttribute("data-current-sku",n.foundProduct.productSku),e.parentElement.parentElement.querySelector(".eseom-order-form-stock").innerHTML=n.foundProduct.productAvailableStock,e.parentElement.parentElement.querySelector(".eseom-order-form-delivery-time").innerHTML=n.foundProduct.productDeliveryTxt,e.parentElement.parentElement.querySelector(".eseom-order-form-price").innerHTML=n.foundProduct.productPriceFormatted+" "+n.foundProduct.productPriceCurrency+r,e.parentElement.parentElement.querySelector(".eseom-order-form-total-price").innerHTML=(Math.round(n.foundProduct.productPrice*parseInt(o.value)*100)/100).toLocaleString(void 0,{minimumFractionDigits:2,maximumFractionDigits:2})+" "+n.foundProduct.productPriceCurrency+r,e.parentElement.parentElement.querySelector(".eseom-order-form-total-price").setAttribute("data-productTotalPrice",n.foundProduct.productPrice*parseInt(o.value)),e.parentElement.parentElement.querySelector(".eseom-order-form-product-cover-td").innerHTML='<img src="'+n.foundProduct.productCoverImageUrl+'" alt="Produktbild" class="img-fluid">';let d=n.foundProduct.productName+'<br><span class="ean-text">'+n.foundProduct.productEAN+"</span>";e.parentElement.parentElement.querySelector(".eseom-order-form-product-name-td").innerHTML=d,l.innerHTML=(Math.round(100*t.getTotalPriceSum())/100).toLocaleString(void 0,{minimumFractionDigits:2,maximumFractionDigits:2}),i.innerHTML=n.foundProduct.productPriceCurrency+r}})),document.activeElement===e&&(void 0===n.foundProductSuggestions||void 0!==n.foundProductSuggestions&&0===n.foundProductSuggestions.length||void 0!==n.foundProduct&&void 0!==n.foundProduct.productSku&&1===n.foundProductSuggestions.length)&&t.hideFoundProductsDiv(),void 0!==n.foundProductSuggestions&&n.foundProductSuggestions.length&&n.foundProductSuggestions.length>1||void 0!==n.foundProduct&&void 0===n.foundProduct.productSku&&n.foundProductSuggestions.length>0){let r=document.querySelector("#foundProductsDiv"),o=e.parentElement.parentElement.querySelector(".eseom-order-form-number").innerHTML,d='<div class="eseom-order-form-found-products-header">';d+='<div class="eseom-order-form-found-products-header-search">'+r.getAttribute("data-productSearchText")+"</div>",d+='<button type="button" id="eseom-order-form-found-products-button-close" class="btn btn-close"></button>',d+='<div id="eseom-order-form-found-products-corresponding-input">'+o+"</div>",d+="</div>",n.foundProductSuggestions.forEach((function(e,t,r){d+='<div class="eseom-order-form-found-products-product">',d+='<div class="eseom-order-form-found-products-product-img"><img src="'+e.productCoverImageUrl+'" alt="Bild" class="img-fluid"></div>',d+='<div class="eseom-order-form-found-products-product-name-number-wrapper">',d+='<div class="eseom-order-form-found-products-product-name">'+n.foundProductSuggestions[t].name+"</div>",""!==n.foundProductSuggestions[t].ean&&(d+='<div class="eseom-order-form-found-products-product-ean ean-text">'+n.foundProductSuggestions[t].ean+"</div>"),d+='<div class="eseom-order-form-found-products-product-number">'+document.querySelector("#foundProductsDiv").getAttribute("data-productNumberText")+" "+e.number+"</div>",e.productVariationOptions.length>0&&(d+='<div class="eseom-order-form-found-products-product-options">'+e.productVariationOptionsTxt+"</div>"),d+="</div>",d+="</div>"})),r.innerHTML=d;let a=e.getBoundingClientRect();r.style.left=a.left+window.scrollX+"px",r.style.top=a.top+a.height+window.scrollY+"px",t.bindCloseSearchBtn(),t.bindAddFoundProduct(),t.showFoundProductsDiv()}t.downloadStatus()}))}onChooseVariantBtnClicked(e){let t=this,r=e.currentTarget;t.loadingModalInstance.show();let o=document.querySelector("#eseom-order-form-get-product-variations-ajax-path").value,n={};n.productNumber=r.getAttribute("data-product-number");let d=JSON.stringify(n);t.client.post(o,d,(e=>{var o=JSON.parse(e);1!==parseInt(o.success)?t.showErrorModalWithMessage(o.msg):t.showChooseVariantModalWithMessage(o.variationHtmlTable,r.parentElement.parentElement.querySelector(".eseom-order-form-sku-input"))}))}showOffcanvasCart(){this.loadingModalInstance.hide();let e=new Event("click");document.getElementsByClassName("header-cart")[0].dispatchEvent(e);let t=new Event("touchstart");document.getElementsByClassName("header-cart")[0].dispatchEvent(t),this.refreshAllPricesInTable()}showCart(){window.location.href="/checkout/cart"}showConfirm(){window.location.href="/checkout/confirm"}refreshAllPricesInTable(){let e=this,t=[];Array.from(document.querySelectorAll(".eseom-order-form-quantity-input")).forEach((r=>{let o=r.parentElement.parentElement.querySelector(".eseom-order-form-sku-input").value;""!==o&&!t.includes(o)&&""!==r.value&&parseInt(r.value)>0&&(t.push(o),e.showSearchSuggestions=!1,e.getPrice(r))}))}addNewPosition(e,t){let r="";r+="<tbody>",r+='<tr class="eseom-order-form-table-tbody-tr">',r+='<th class="eseom-order-form-number align-middle" scope="row">'+(document.querySelectorAll(".eseom-order-form-table-tbody-tr").length+1)+"</th>",r+='<td class="eseom-order-form-product-cover-td align-middle">-</td>',r+='<td class="align-middle"><input class="eseom-order-form-sku-input form-control" type="text"  value="'+e+'"placeholder="'+document.querySelector("#eseom-order-form-table").getAttribute("data-skuPlaceholder")+'"></td>',r+='<td class="eseom-order-form-product-name-td align-middle">-</td>',r+='<td class="align-middle"><input class="eseom-order-form-quantity-input form-control" type="number" value="'+t+'" min="1" step="1" placeholder="'+document.querySelector("#eseom-order-form-table").getAttribute("data-quantityPlaceholder")+'"></td>',r+='<td class="eseom-order-form-stock align-middle">-</td>',r+='<td class="eseom-order-form-delivery-time align-middle">-</td>',r+='<td class="eseom-order-form-price align-middle">-</td>',r+='<td class="eseom-order-form-total-price align-middle">-</td>',r+='<td class="eseom-order-form-information align-middle">',r+='<button class="eseom-order-form-action-btn-info btn btn-info" type="button" aria-label="info button">',r+='<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="#fff" fill-rule="evenodd" d="M12,7 C12.5522847,7 13,7.44771525 13,8 C13,8.55228475 12.5522847,9 12,9 C11.4477153,9 11,8.55228475 11,8 C11,7.44771525 11.4477153,7 12,7 Z M13,16 C13,16.5522847 12.5522847,17 12,17 C11.4477153,17 11,16.5522847 11,16 L11,11 C11,10.4477153 11.4477153,10 12,10 C12.5522847,10 13,10.4477153 13,11 L13,16 Z M24,12 C24,18.627417 18.627417,24 12,24 C5.372583,24 6.14069502e-15,18.627417 5.32907052e-15,12 C-8.11624501e-16,5.372583 5.372583,4.77015075e-15 12,3.55271368e-15 C18.627417,5.58919772e-16 24,5.372583 24,12 Z M12,2 C6.4771525,2 2,6.4771525 2,12 C2,17.5228475 6.4771525,22 12,22 C17.5228475,22 22,17.5228475 22,12 C22,6.4771525 17.5228475,2 12,2 Z"></path></svg>',r+="</button>",r+="</td>",r+='<td class="eseom-order-form-delete align-middle">',r+='<button class="eseom-order-form-action-btn-delete btn btn-delete" type="button" aria-label="delete button" title="'+document.querySelector("#eseom-order-form-table").getAttribute("data-titleDelete")+'">',r+='<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 18 18"><path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"></path><path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4L4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"></path></svg>',r+="</button>",r+="</td>",r+="</tr>",r+="</tbody>";var o=document.createElement("table");o.innerHTML=r.trim();let n=o.firstChild.firstChild;return document.querySelector("#eseom-order-form-table tbody").append(n),n}showSuccessModalWithMessage(e){this.loadingModalInstance.hide(),document.querySelector("#eseom-order-form-success-modal .modal-body").innerHTML=e,this.successModalInstance.show()}showInfoModalWithMessage(e){this.loadingModalInstance.hide(),document.querySelector("#eseom-order-form-info-modal .modal-body").innerHTML=e,this.infoModalInstance.show()}showErrorModalWithMessage(e){this.loadingModalInstance.hide(),document.querySelector("#eseom-order-form-error-modal .modal-body").innerHTML=e,this.errorModalInstance.show()}showChooseVariantModalWithMessage(e,t){let r=this;r.loadingModalInstance.hide(),document.querySelector("#eseom-order-form-choose-variant-modal .modal-body").innerHTML=e,r.chooseVariantModalInstance.show(),Array.from(document.querySelectorAll(".eseom-order-form-choose-variant-use-btn")).forEach((e=>{e.addEventListener("click",(e=>{t.value=e.currentTarget.getAttribute("data-variant-order-number"),r.chooseVariantModalInstance.hide(),r.handleSkuInputChange(t)}))}))}}window.PluginManager.register("EseomOrderForm",a,"[data-eseom-order-form]")}},e=>{e.O(0,["vendor-node","vendor-shared"],(()=>{return t=2791,e(e.s=t);var t}));e.O()}]);