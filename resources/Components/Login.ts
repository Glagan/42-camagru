import { User } from '../Auth';
import { Component } from '../Component';
import { Notification } from '../UI/Notification';
import { DOM } from '../Utility/DOM';
import { Http } from '../Utility/Http';

export class Login extends Component {
	static auth = false;

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
			min: '4',
			max: '100',
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
			min: '8',
			max: '72',
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
		DOM.validateInput(this.username, (value) => {
			return value.length >= 4;
		});
		DOM.validateInput(this.password, (value) => {
			return value.length >= 8;
		});
		this.form.addEventListener('submit', async (event) => {
			event.preventDefault();
			if (this.username.value.length < 4) {
				this.username.classList.add('error');
				return;
			}
			if (this.password.value.length < 8) {
				this.password.classList.add('error');
				return;
			}
			const response = await Http.post<{ user: User }, { error: string }>('/api/login', {
				username: this.username.value,
				password: this.password.value,
			});
			if (response.ok) {
				Notification.show('success', 'Logged in !');
				this.application.loggedIn(response.body.user);
			} else {
				Notification.show('danger', `Error: ${response.body.error}`);
			}
		});
	}

	render(): void {
		DOM.append(this.parent, this.header, this.form);
	}
}
