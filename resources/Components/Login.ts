import { Component } from '../Component';
import { DOM } from '../Utility/DOM';

export class Login extends Component {
	header!: HTMLElement;
	form!: HTMLFormElement;
	labelUsername!: HTMLLabelElement;
	username!: HTMLInputElement;
	labelPassword!: HTMLLabelElement;
	password!: HTMLInputElement;
	footer!: HTMLElement;
	forgotPassword!: HTMLButtonElement;
	submit!: HTMLButtonElement;

	create(): void {
		this.header = DOM.create('h1', { className: 'header', textContent: 'Login' });
		this.labelUsername = DOM.create('label', {
			htmlFor: 'login-username',
			textContent: 'Username',
		});
		this.username = DOM.create('input', {
			type: 'text',
			id: 'login-username',
			name: 'username',
			placeholder: 'Username',
		});
		this.labelPassword = DOM.create('label', {
			htmlFor: 'login-password',
			textContent: 'Password',
		});
		this.password = DOM.create('input', {
			type: 'password',
			id: 'login-password',
			name: 'password',
			placeholder: 'Password',
		});
		this.forgotPassword = DOM.button('secondary', 'at-symbol', 'Forgot Password');
		this.submit = DOM.button('primary', 'login', 'Login');
		this.footer = DOM.create('div', { className: 'footer', childs: [this.submit, this.forgotPassword] });
		this.form = DOM.create('form', {
			className: 'flex flex-col flex-wrap items-stretch',
			childs: [this.labelUsername, this.username, this.labelPassword, this.password, this.footer],
		});
	}

	bind(): void {
		this.link(this.forgotPassword, '/forgot-password');
	}

	render(): void {
		DOM.append(this.parent, this.header, this.form);
	}
}
