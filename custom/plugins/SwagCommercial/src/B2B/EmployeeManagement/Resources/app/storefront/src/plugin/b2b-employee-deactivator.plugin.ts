import Plugin from 'src/plugin-system/plugin.class';
import ElementLoadingIndicatorUtil from 'src/utility/loading-indicator/element-loading-indicator.util';
import HttpClient from 'src/service/http-client.service';
import DomAccess from 'src/helper/dom-access.helper';

type Options = {
    contentId?: string,
    confirmButtonId?: string,
};

type EmployeeData = {
    id?: string,
    firstName?: string,
    lastName?: string,
};

type EmployeeDeactivateModalDataset = {
    deactivateAction?: string,
    contentSnippet?: string,
    employee?: EmployeeData,
};

export default class B2bEmployeeDeactivator extends Plugin {
    static options: Options = {
        contentId: null,
        confirmButtonId: null,
    };

    private el: HTMLElement;

    private deactivateAction?: string;

    private employee?: EmployeeData;

    public init(): void {
        this.client = new HttpClient();
        this.el.addEventListener('show.bs.modal', (event: PointerEvent) => this._onModalShow(event));

        const confirmDeactivateButton = DomAccess.querySelector(this.el, `#${this.options.confirmButtonId}`);
        confirmDeactivateButton.addEventListener('click', this._deactivateEmployee.bind(this));
    }

    private _onModalShow(event: PointerEvent): void {
        const dataset = (event.relatedTarget as HTMLButtonElement).dataset as EmployeeDeactivateModalDataset;
        const employee = JSON.parse(dataset.employee as string) as EmployeeData;

        const hasRequiredData = [dataset.deactivateAction, dataset.contentSnippet, employee.firstName, employee.lastName]
            .every(value => value);
        if (!hasRequiredData) {
            throw new DOMException('Failed to update modal content. Required data attributes are missing.');
        }

        this.deactivateAction = dataset.deactivateAction;

        const contentElement = DomAccess.querySelector(event.target, `#${this.options.contentId}`);
        contentElement.classList.remove('text-center');
        contentElement.innerHTML = dataset.contentSnippet;

        this.employee = employee;
    }

    private _deactivateEmployee(): void {
        ElementLoadingIndicatorUtil.create(this.el);

        const url = new URL(location.href);

        url.searchParams.set('deactivate', 'true');
        url.searchParams.set('firstName', this.employee.firstName);
        url.searchParams.set('lastName', this.employee.lastName);

        this.client.get(this.deactivateAction, () => {
            location.assign(url.toString());
        });
    }
}
