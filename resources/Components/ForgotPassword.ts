import { Component } from '../Component';
import { DOM } from '../Utility/DOM';

export class ForgotPassword extends Component {
	static auth = false;

	header!: HTMLElement;
	form!: HTMLFormElement;
	labelEmail!: HTMLLabelElement;
	email!: HTMLInputElement;
	footer!: HTMLElement;
	submit!: HTMLButtonElement;

	create(): void {
		this.header = DOM.create('h1', { className: 'header', textContent: 'Password Reset' });
		// TODO: Alert "Enter the email linked to your account to receive a unique link to reset your password."
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
			childs: [this.labelEmail, this.email, this.footer],
		});
	}

	bind(): void {}

	render(): void {
		DOM.append(this.parent, this.header, this.form);
	}
}
