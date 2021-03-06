import { Component } from '../Component';
import { Notification } from '../UI/Notification';
import { DOM } from '../Utility/DOM';
import { Http } from '../Utility/Http';
import { Validator } from '../Utility/Validator';

export class Verify extends Component {
	static auth = true;

	header!: HTMLElement;
	form!: HTMLFormElement;
	labelCode!: HTMLLabelElement;
	code!: HTMLInputElement;
	footer!: HTMLElement;
	submit!: HTMLButtonElement;
	sendAgain!: HTMLButtonElement;
	sendAgainTimeout: number = 0;

	create(): void {
		this.header = DOM.create('h1', { className: 'header', textContent: 'Verify Email' });
		this.labelCode = DOM.create('label', {
			htmlFor: 'validate-code',
			textContent: 'Code',
		});
		this.code = DOM.create('input', {
			type: 'text',
			id: 'validate-code',
			name: 'code',
			placeholder: 'Code (50 characters)',
			min: '50',
			max: '50',
			required: true,
		});
		this.sendAgain = DOM.button('secondary', 'at-symbol', 'Send Verification');
		this.submit = DOM.button('primary', 'check', 'Validate');
		this.footer = DOM.create('div', { className: 'footer', childs: [this.submit, this.sendAgain] });
		this.form = DOM.create('form', {
			className: 'flex flex-col flex-wrap items-stretch',
			childs: [this.labelCode, this.code, this.footer],
		});
		this.validators.code = new Validator(this.code, (value) => {
			return value.length !== 50 ? 'The code must be 50 characters long.' : true;
		});
	}

	bind(): void {
		this.sendAgain.addEventListener('click', async (event) => {
			event.preventDefault();
			if (this.sendAgain.disabled) return;
			this.sendAgain.disabled = true;
			const response = await Http.put<{ success: string }>('/api/account/send-verification');
			if (response.ok) {
				Notification.show('success', response.body.success);
				this.sendAgainTimeout = setTimeout(() => {
					this.sendAgain.disabled = false;
				}, 1000 * 60 * 10); // 10min
			} else {
				Notification.show('danger', response.body.error);
				this.sendAgain.disabled = false;
			}
		});
		this.form.addEventListener('submit', async (event) => {
			event.preventDefault();
			this.runOnce(
				this.form,
				async () => {
					if (!this.validate()) return;
					// Send the request
					const response = await Http.patch<{ success: string }>('/api/account/verify', {
						code: this.code.value.trim(),
					});
					if (response.ok) {
						Notification.show('success', response.body.success);
						this.application.auth.user.verified = true;
						this.application.navigate('/');
					} else {
						Notification.show('danger', `Error: ${response.body.error}`);
					}
				},
				[this.code, this.submit, this.sendAgain]
			);
		});
	}

	render(): void {
		if (this.application.auth.user.verified) {
			this.application.navigate('/');
			return;
		}
		if (this.application.currentMatch?.query.code) {
			this.code.value = this.application.currentMatch.query.code;
		}
		DOM.append(this.parent, this.header, this.form);
	}

	destroy(): void {
		clearTimeout(this.sendAgainTimeout);
	}
}
