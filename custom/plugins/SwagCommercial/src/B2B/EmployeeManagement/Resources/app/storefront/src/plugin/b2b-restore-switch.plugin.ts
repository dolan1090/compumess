import Plugin from 'src/plugin-system/plugin.class';
import DomAccess from 'src/helper/dom-access.helper';

type Options = {
    defaultRoleSwitchId?: string,
    confirmButtonId?: string,
};

export default class B2bRestoreSwitch extends Plugin {
    static options: Options = {
        defaultRoleSwitchId: null,
        confirmButtonId: null,
    };

    private el: HTMLElement;

    private changeConfirmed = false;

    public init(): void {
        const confirmButton = DomAccess.querySelector(this.el, `#${this.options.confirmButtonId}`);
        confirmButton.addEventListener('click', this._setChangeConfirmed.bind(this));

        this.el.addEventListener('hidden.bs.modal', this._restore.bind(this));
    }

    private _setChangeConfirmed(): void {
        this.changeConfirmed = true;
    }

    private _restore(): void {
        if (this.changeConfirmed) {
            this.changeConfirmed = false;
            return;
        }

        const switchElement = DomAccess.querySelector(document, `#${this.options.defaultRoleSwitchId}`);
        if (switchElement?.type !== 'checkbox') {
            throw new DOMException(`Target input is not a checkbox: "#${this.options.defaultRoleSwitchId}"`);
        }

        switchElement.checked = false;
    }
}
