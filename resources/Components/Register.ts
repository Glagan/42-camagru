import { Component } from '../Component';
import { DOM } from '../Utility/DOM';

export class Register extends Component {
	header!: HTMLElement;
	form!: HTMLFormElement;
	labelUsername!: HTMLLabelElement;
	username!: HTMLInputElement;
	labelEmail!: HTMLLabelElement;
	email!: HTMLInputElement;
	labelPassword!: HTMLLabelElement;
	password!: HTMLInputElement;
	labelConfirmPassword!: HTMLLabelElement;
	confirmPassword!: HTMLInputElement;
	footer!: HTMLElement;
	submit!: HTMLButtonElement;

	create(): void {
		this.header = DOM.create('h1', { className: 'header', textContent: 'Register' });
		this.form = DOM.create('form', { className: 'flex flex-col flex-wrap items-stretch' });
		this.labelUsername = DOM.create('label', {
			htmlFor: 'register-username',
			textContent: 'Username',
		});
		this.username = DOM.create('input', {
			type: 'text',
			id: 'register-username',
			name: 'username',
			placeholder: 'Username',
		});
		this.labelEmail = DOM.create('label', {
			htmlFor: 'register-email',
			textContent: 'Email',
		});
		this.email = DOM.create('input', {
			type: 'email',
			id: 'register-email',
			name: 'email',
			placeholder: 'Email',
		});
		this.labelPassword = DOM.create('label', {
			htmlFor: 'register-password',
			textContent: 'Password',
		});
		this.password = DOM.create('input', {
			type: 'password',
			id: 'register-password',
			name: 'password',
			placeholder: 'Password',
		});
		this.labelConfirmPassword = DOM.create('label', {
			htmlFor: 'register-confirm-password',
			textContent: 'Confirm Password',
		});
		this.confirmPassword = DOM.create('input', {
			type: 'password',
			id: 'register-confirm-password',
			name: 'confirm-password',
			placeholder: 'Confirm Password',
		});
		this.footer = DOM.create('div', { className: 'footer' });
		this.submit = DOM.button('primary', 'user-add', 'Register');
	}

	bind(): void {}

	render(): void {
		DOM.append(this.footer, this.submit);
		DOM.append(
			this.form,
			this.labelUsername,
			this.username,
			this.labelEmail,
			this.email,
			this.labelPassword,
			this.password,
			this.labelConfirmPassword,
			this.confirmPassword,
			this.footer
		);
		DOM.append(this.parent, this.header, this.form);
	}
}
