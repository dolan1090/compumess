import Plugin from 'src/plugin-system/plugin.class';
import HttpClient from 'src/service/http-client.service';
import ElementLoadingIndicatorUtil from 'src/utility/loading-indicator/element-loading-indicator.util';
import DomAccess from 'src/helper/dom-access.helper';

type Options = {
    contentId?: string,
    confirmButtonId?: string,
};

export default class B2bDeleter extends Plugin {
    static options: Options = {
        contentId: null,
        confirmButtonId: null,
    };

    private el: HTMLElement;

    private deleteAction: string;

    private contentSnippet: string;

    public init(): void {
        this.el.addEventListener('show.bs.modal', this._showModalEventListener.bind(this));

        const confirmDeleteButton = DomAccess.querySelector(this.el, `#${this.options.confirmButtonId}`);
        confirmDeleteButton.addEventListener('click', this._deleteEmployee.bind(this));
    }

    private _showModalEventListener(event: PointerEvent): void {
        this.deleteAction = event.relatedTarget.dataset.deleteAction;
        this.contentSnippet = event.relatedTarget.dataset.contentSnippet;

        const hasRequiredData = this.deleteAction && this.contentSnippet;
        if (!hasRequiredData) {
            throw new DOMException('Failed to update content. Required data attributes are missing.');
        }

        const contentElement = DomAccess.querySelector(event.target, `#${this.options.contentId}`);
        contentElement.classList.remove('text-center');
        contentElement.innerHTML = this.contentSnippet;
    }

    private _deleteEmployee(): void {
        ElementLoadingIndicatorUtil.create(this.el);
        const httpClient = new HttpClient();

        httpClient.delete(this.deleteAction, null, () => {
            location.reload();
        });
    }
}
