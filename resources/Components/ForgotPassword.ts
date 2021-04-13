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
	codePage!: HTMLAnchorElement;

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
			required: true,
		});
		this.submit = DOM.button('primary', 'at-symbol', 'Send Reset Link');
		this.codePage = DOM.link('secondary', 'clipboard-list', 'I already have a code', '/reset-password');
		this.footer = DOM.create('div', { className: 'footer', childs: [this.submit, this.codePage] });
		this.form = DOM.create('form', {
			className: 'flex flex-col flex-wrap items-stretch',
			childs: [this.alert, this.labelEmail, this.email, this.footer],
		});
		this.validators.email = new Validator(this.email, Validator.email);
	}

	bind(): void {
		this.link(this.codePage);
		this.form.addEventListener('submit', async (event) => {
			event.preventDefault();
			this.runOnce(
				this.form,
				async () => {
					if (!this.validate()) return;
					const response = await Http.post<{ success: string }>('/api/account/forgot-password', {
						email: this.email.value.trim(),
					});
					if (response.ok) {
						Notification.show('success', response.body.success);
						this.email.value = '';
					} else {
						Notification.show('danger', response.body.error);
					}
				},
				[this.email, this.submit, this.codePage]
			);
		});
	}

	render(): void {
		DOM.append(this.parent, this.header, this.form);
	}
}
