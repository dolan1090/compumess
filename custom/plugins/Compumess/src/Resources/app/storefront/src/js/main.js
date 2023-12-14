
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
filterBtnCustom();

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
manufacture_h();
