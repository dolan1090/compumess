
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

