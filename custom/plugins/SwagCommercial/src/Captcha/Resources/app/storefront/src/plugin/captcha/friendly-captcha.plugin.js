import Plugin from 'src/plugin-system/plugin.class';

export default class FriendlyCaptchaPlugin extends Plugin {
    static options = {
        siteKey: null,
        startMode: null,
        language: null,
        frcContainerSelector: '.frc-captcha',
        solutionFieldName: 'frc-captcha-solution',
        frcHasErrorClassSelector: 'has-error',
        frcHasSuccessClassSelector: 'has-success',
    };

    init() {
        this._getForm();

        if (!this._form || !this.options.siteKey) {
            return;
        }

        this.frcContainerSelector = this.el.querySelector(this.options.frcContainerSelector);

        this.friendlyChallenge = window.friendlyChallenge;
        this._formSubmitting = false;
        this.formPluginInstances = window.PluginManager.getPluginInstancesFromElement(this._form);

        this._registerEvents();

        this._render();
    }

    onFormSubmit() {
        if (this.widget && this.widget.valid) {
            this._submitInvisibleForm();

            return;
        }

        this.frcContainerSelector.classList.add(this.options.frcHasErrorClassSelector);

        this._formSubmitting = true;
    }

    _render() {
        if (!this.friendlyChallenge) {
            return;
        }

        if (this.widget && this.widget.valid) {
            return;
        }

        this.widget = new this.friendlyChallenge.WidgetInstance(this.frcContainerSelector, {
            siteKey: this.options.siteKey,
            startMode: this.options.startMode,
            language: this.options.language,
            doneCallback: this._onCaptchaTokenResponse.bind(this),
            readyCallback: this._onCaptchaReadyCallback.bind(this),
        });
    }

    _onCaptchaTokenResponse(solution) {
        if (!solution) {
            this._formSubmitting = true;
            return;
        }

        this.frcContainerSelector.classList.remove(this.options.frcHasErrorClassSelector);
        this.frcContainerSelector.classList.add(this.options.frcHasSuccessClassSelector);
    }

    _onCaptchaReadyCallback() {
        this.frcInputSelector = this.el.querySelector(`[name="${this.options.solutionFieldName}"]`);

        this.frcInputSelector.value = '';
        this.frcInputSelector.setAttribute('data-skip-report-validity', true);
        this.frcInputSelector.setAttribute('required', true);
        this.frcInputSelector.setAttribute('type', 'text');
    }

    _registerEvents() {
        if (!this.formPluginInstances) {
            this._form.addEventListener('submit', this._onFormSubmitCallback.bind(this));
        } else {
            this.formPluginInstances.forEach(plugin => {
                plugin.$emitter.subscribe('beforeSubmit', this._onFormSubmitCallback.bind(this));
            });
        }
    }

    _onFormSubmitCallback() {
        if (this._formSubmitting) {
            return;
        }

        this._formSubmitting = true;

        this.onFormSubmit()
    }

    _getForm() {
        if (this.el && this.el.nodeName === 'FORM') {
            this._form = this.el;
            return true;
        }

        this._form = this.el.closest('form');

        return this._form;
    }

    _submitInvisibleForm() {
        if (!this._form.checkValidity()) {
            return;
        }

        let ajaxSubmitFound = false;

        this.formPluginInstances.forEach(plugin => {
            if (typeof plugin.sendAjaxFormSubmit === 'function' && plugin.options.useAjax !== false) {
                ajaxSubmitFound = true;
                plugin.sendAjaxFormSubmit();
            }
        });

        if (ajaxSubmitFound) {
            return;
        }

        this._form.submit();
    }
}
