import { Component } from '../Component';
import { Notification } from '../UI/Notification';
import { DOM } from '../Utility/DOM';
import { Http } from '../Utility/Http';
import { Validator } from '../Utility/Validator';

export class ResetPassword extends Component {
	static auth = false;

	header!: HTMLElement;
	form!: HTMLFormElement;
	labelCode!: HTMLLabelElement;
	code!: HTMLInputElement;
	labelPassword!: HTMLLabelElement;
	password!: HTMLInputElement;
	labelConfirmPassword!: HTMLLabelElement;
	confirmPassword!: HTMLInputElement;
	footer!: HTMLElement;
	submit!: HTMLButtonElement;

	create(): void {
		this.header = DOM.create('h1', { className: 'header', textContent: 'Password Reset' });
		this.labelCode = DOM.create('label', {
			htmlFor: 'reset-code',
			textContent: 'Code',
		});
		this.code = DOM.create('input', {
			type: 'text',
			id: 'reset-code',
			name: 'code',
			placeholder: 'Code',
			min: '50',
			max: '50',
		});
		this.labelPassword = DOM.create('label', {
			htmlFor: 'reset-password',
			textContent: 'Password',
		});
		this.password = DOM.create('input', {
			type: 'password',
			id: 'reset-password',
			name: 'password',
			placeholder: 'New password',
			min: '8',
			max: '72',
		});
		this.labelConfirmPassword = DOM.create('label', {
			htmlFor: 'reset-confirm-password',
			textContent: 'Confirm Password',
		});
		this.confirmPassword = DOM.create('input', {
			type: 'password',
			id: 'reset-confirm-password',
			name: 'confirm-password',
			placeholder: 'Confirm Password',
			min: '8',
			max: '72',
		});
		this.submit = DOM.button('primary', 'at-symbol', 'Send Reset Link');
		this.footer = DOM.create('div', { className: 'footer', childs: [this.submit] });
		this.form = DOM.create('form', {
			className: 'flex flex-col flex-wrap items-stretch',
			childs: [
				this.labelCode,
				this.code,
				this.labelPassword,
				this.password,
				this.labelConfirmPassword,
				this.confirmPassword,
				this.footer,
			],
		});
		this.validators.code = new Validator(this.code, (value) => {
			return value.length !== 50 ? 'The code must be 50 characters long.' : true;
		});
		this.validators.password = new Validator(this.password, Validator.password);
		this.validators.confirmPassword = new Validator(this.confirmPassword, (value) => {
			return value !== this.password.value.trim() ? 'Password does not match.' : true;
		});
	}

	bind(): void {
		this.form.addEventListener('submit', async (event) => {
			event.preventDefault();
			if (!this.validate()) return;
			const response = await Http.patch<{ success: string }>('/api/account/reset-password', {
				code: this.code.value.trim(),
				password: this.password.value.trim(),
			});
			if (response.ok) {
				Notification.show('success', response.body.success);
				this.application.navigate('/login');
			} else {
				Notification.show('danger', response.body.error);
			}
		});
	}

	render(): void {
		DOM.append(this.parent, this.header, this.form);
	}
}
