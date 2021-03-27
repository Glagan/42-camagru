import { Component } from '../Component';
import { Alert } from '../UI/Alert';
import { Notification } from '../UI/Notification';
import { DOM } from '../Utility/DOM';
import { Http } from '../Utility/Http';
import { Validator } from '../Utility/Validator';

export class ForgotPassword extends Component {
	static auth = false;

	header!: HTMLElement;
	alert!: HTMLElement;
	form!: HTMLFormElement;
	labelEmail!: HTMLLabelElement;
	email!: HTMLInputElement;
	footer!: HTMLElement;
	submit!: HTMLButtonElement;

	create(): void {
		this.header = DOM.create('h1', { className: 'header', textContent: 'Password Reset' });
		this.alert = Alert.make(
			'info',
			'Enter the email linked to your account to receive a unique link to reset your password.'
		);
		this.labelEmail = DOM.create('label', {
			htmlFor: 'forget-email',
			textContent: 'Email',
		});
		this.email = DOM.create('input', {
			type: 'email',
			id: 'forget-email',
			name: 'email',
			placeholder: 'Email',
		});
		this.submit = DOM.button('primary', 'at-symbol', 'Send Reset Link');
		this.footer = DOM.create('div', { className: 'footer', childs: [this.submit] });
		this.form = DOM.create('form', {
			className: 'flex flex-col flex-wrap items-stretch',
			childs: [this.alert, this.labelEmail, this.email, this.footer],
		});
		this.validators.email = new Validator(this.email, Validator.email);
	}

	bind(): void {
		this.form.addEventListener('submit', async (event) => {
			event.preventDefault();
			if (!this.validate()) return;
			const response = await Http.post<{ success: string }>('/api/forgot-password', { email: this.email.value });
			if (response.ok) {
				Notification.show('success', response.body.success);
				this.email.value = '';
			} else {
				Notification.show('danger', response.body.error);
			}
		});
	}

	render(): void {
		DOM.append(this.parent, this.header, this.form);
	}
}
