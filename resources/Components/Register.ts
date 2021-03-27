import { Component } from '../Component';
import { Notification } from '../UI/Notification';
import { DOM } from '../Utility/DOM';
import { Http } from '../Utility/Http';
import { Theme } from '../Utility/Theme';
import { Validator } from '../Utility/Validator';

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
		this.labelUsername = DOM.create('label', {
			htmlFor: 'register-username',
			textContent: 'Username',
		});
		this.username = DOM.create('input', {
			type: 'text',
			id: 'register-username',
			name: 'username',
			placeholder: 'Username',
			min: '4',
			max: '100',
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
			required: true,
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
			min: '8',
			max: '72',
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
			min: '8',
			max: '72',
		});
		this.submit = DOM.button('primary', 'user-add', 'Register');
		this.footer = DOM.create('div', { className: 'footer', childs: [this.submit] });
		this.form = DOM.create('form', {
			className: 'flex flex-col flex-wrap items-stretch',
			childs: [
				this.labelUsername,
				this.username,
				this.labelEmail,
				this.email,
				this.labelPassword,
				this.password,
				this.labelConfirmPassword,
				this.confirmPassword,
				this.footer,
			],
		});
		this.validators.username = new Validator(this.username, Validator.username);
		this.validators.password = new Validator(this.password, Validator.password);
		this.validators.email = new Validator(this.email, Validator.email);
		this.validators.confirmPassword = new Validator(this.confirmPassword, (value) => {
			return value !== this.password.value ? 'Password does not match.' : true;
		});
	}

	bind(): void {
		this.form.addEventListener('submit', async (event) => {
			event.preventDefault();
			if (!this.validate()) return;
			const response = await Http.post<{ user: User }>('/api/register', {
				username: this.username.value,
				email: this.email.value,
				password: this.password.value,
				theme: Theme.value,
			});
			if (response.ok) {
				Notification.show('success', 'Account created !');
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
