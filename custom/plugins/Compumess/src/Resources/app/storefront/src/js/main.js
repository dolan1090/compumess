document.addEventListener('DOMContentLoaded', function () {
    filterBtnCustom();
    manufacture_h();
    addToCartInquiry();
    addToCartInquiry_category();
});

//category product
function filterBtnCustom() {
    var filterTriggerBtn = document.querySelector('.filter-panel-wrapper .action--filter-btn');
    var filterPanelItemsContainer = document.querySelector('.filter-panel-wrapper .filter-panel .filter-panel-items-container');

    if (filterTriggerBtn) {
        filterTriggerBtn.addEventListener('click', function () {
            filterTriggerBtn.classList.toggle('filter-actived');
            filterPanelItemsContainer.classList.toggle('active');
        });
    }
}

function addToCartInquiry_category() {
    var inquiryPriceInfoList = document.querySelectorAll('.product-price-info');

    inquiryPriceInfoList.forEach(function(inquiryPriceInfo) {
        var inquiryPrice = inquiryPriceInfo.querySelector('.product-price-wrapper .product-price');

        if (inquiryPrice && inquiryPrice.innerHTML.trim() === 'Preis auf Anfrage') {
            inquiryPriceInfo.style.display = 'none';
        }
    });
}


//hom page
function manufacture_h() {
    var manufacture_h_items = document.querySelectorAll('.manufacture-h .row .col-md-4');

    if (manufacture_h_items) {
        for (let i = 0; i < manufacture_h_items.length; i++) {
            var manufacture_h_element_text = manufacture_h_items[i].querySelector('.cms-element-text');

            if (manufacture_h_element_text.textContent.trim() === '') {
                manufacture_h_items[i].style.display = "none";
            }
        }
    }
}

//detail product
function addToCartInquiry() {
    var inquiryPrice = document.querySelector('.product-detail-content .inquiry-price');
    var quaity = document.querySelector('.product-detail-form-container form .col-4.d-flex.justify-content-end')
    var rowInquiryPrice = document.querySelector('.product-detail-form-container .row.g-2.buy-widget-container.mt-2');

    if (inquiryPrice && inquiryPrice.innerHTML == 'Preis auf Anfrage') {
        inquiryPrice.style.display = 'none';

        if (quaity) {
            quaity.classList.add('d-none');
        }

        if (rowInquiryPrice) {
            rowInquiryPrice.classList.remove('mt-2');
        }
    }
}

//SEO
document.addEventListener('DOMContentLoaded', function() {

    var titleTag = document.querySelector('title');
    if (titleTag) {
        titleTag.innerText = replaceSpecialCharacters(titleTag.innerText);
    }

    var metaDescriptionTag = document.querySelector('meta[name="description"]');
    if (metaDescriptionTag) {
        metaDescriptionTag.content = replaceSpecialCharacters(metaDescriptionTag.content);
    }

    function replaceSpecialCharacters(text) {
        if (text.includes('&amp;')) {
            text = text.replace(/&amp;/g, '&');
        }
        if (text.includes('&gt;')) {
            text = text.replace(/&gt;/g, '>');
        }
        if (text.includes('&lt;')) {
            text = text.replace(/&lt;/g, '<');
        }
        return text;
    }
});

function lineCardCME() {
    var currentPath = window.location.pathname;

    if (currentPath.match(/\/line-card-cme\.pdf/i)) {
        var newPath = currentPath.replace(/\/line-card-cme\.pdf/i, '/Line-Card-CME.pdf');
        window.location.href = newPath;
    }
}
lineCardCME();

document.addEventListener('DOMContentLoaded', function() {
    //handbuch-anfragen-page
    const handbuchAnfragenPageArea = document.querySelector('.handbuch-anfragen-page textarea.form-control');
    if (handbuchAnfragenPageArea) {
        const getCurrentProductNumber = localStorage.getItem('currentProductNumber');
        const getCurrentProductURL = localStorage.getItem('currentProductURL');
        handbuchAnfragenPageArea.value = 'Ich wünsche das Handbuch zu folgendem Artikel und bitte um zeitnahe Kontaktaufnahme:' + '\n' + getCurrentProductURL + '\n' + '(' + getCurrentProductNumber + ')';
    }

    //News White Paper
    const newsWhitePaperForm = document.querySelector('.newsWhitePaperForm form');
    const newsWhitePaperHeading = document.querySelector('h1.forms--title');
    if (newsWhitePaperForm && newsWhitePaperHeading) {
        const newsWhitePaperFormTitle = newsWhitePaperForm.querySelector('.card a.card-title');
        const newsWhitePaperFormArea = newsWhitePaperForm.querySelector('textarea.form-control');
        newsWhitePaperFormTitle.textContent = newsWhitePaperHeading.textContent;    
        newsWhitePaperFormArea.value = 'Ich interessiere mich für das White Paper dieses Produkts. Bitte lassen Sie mir dieses zeitnah zukommen.'
    }
});
