import { Component } from '../Component';
import { Alert } from '../UI/Alert';
import { Notification } from '../UI/Notification';
import { Toggle } from '../UI/Toggle';
import { DOM } from '../Utility/DOM';
import { Http } from '../Utility/Http';
import { Validator } from '../Utility/Validator';

export class Account extends Component {
	static auth = true;

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
	labelCurrentPassword!: HTMLLabelElement;
	currentPassword!: HTMLInputElement;
	receiveCommentsToggle!: { label: HTMLLabelElement; checkbox: HTMLInputElement };
	footer!: HTMLElement;
	submit!: HTMLButtonElement;
	logoutHeader!: HTMLElement;
	logoutInformation!: HTMLElement;
	logoutWrapper!: HTMLElement;
	logoutButton!: HTMLButtonElement;

	create(): void {
		this.header = DOM.create('h1', { className: 'header', textContent: 'Account Informations' });
		this.labelUsername = DOM.create('label', {
			htmlFor: 'update-username',
			textContent: 'Username',
		});
		this.username = DOM.create('input', {
			type: 'text',
			id: 'update-username',
			name: 'username',
			placeholder: 'Username',
		});
		this.labelEmail = DOM.create('label', {
			htmlFor: 'update-username',
			textContent: 'Email',
		});
		this.email = DOM.create('input', {
			type: 'text',
			id: 'update-email',
			name: 'email',
			placeholder: 'Email',
		});
		this.labelPassword = DOM.create('label', {
			htmlFor: 'update-password',
			textContent: 'Password',
		});
		this.password = DOM.create('input', {
			type: 'password',
			id: 'update-password',
			name: 'password',
			placeholder: 'Password',
		});
		this.labelConfirmPassword = DOM.create('label', {
			htmlFor: 'update-confirm-password',
			textContent: 'Confirm Password',
		});
		this.confirmPassword = DOM.create('input', {
			type: 'password',
			id: 'update-confirm-password',
			name: 'confirm-password',
			placeholder: 'Confirm new Password',
		});
		this.labelCurrentPassword = DOM.create('label', {
			htmlFor: 'current-password',
			textContent: 'Current Password',
		});
		this.currentPassword = DOM.create('input', {
			type: 'password',
			id: 'current-password',
			name: 'password',
			placeholder: 'Current Password',
		});
		this.receiveCommentsToggle = Toggle.make('Receive Comments', { checked: true });
		this.submit = DOM.button('primary', 'save', 'Save');
		this.footer = DOM.create('div', { className: 'footer', childs: [this.submit] });
		this.form = DOM.create('form', {
			className: 'flex flex-col flex-wrap items-stretch',
			childs: [
				Alert.make(
					'info',
					`You can edit only the field you want to update and press Submit.\nYou always need to set your current password to update your informations.`
				),
				this.labelUsername,
				this.username,
				this.labelEmail,
				this.email,
				this.labelPassword,
				this.password,
				this.labelConfirmPassword,
				this.confirmPassword,
				this.receiveCommentsToggle.label,
				this.labelCurrentPassword,
				this.currentPassword,
				this.footer,
			],
		});
		this.validators.user = new Validator(this.username, Validator.username);
		this.validators.email = new Validator(this.email, Validator.email);
		this.validators.password = new Validator(this.password, Validator.password);
		this.validators.confirmPassword = new Validator(this.confirmPassword, (value) => {
			return value !== this.password.value ? 'Password does not match.' : true;
		});
		this.validators.currentPassword = new Validator(this.currentPassword, Validator.password);
		this.logoutHeader = DOM.create('h1', { className: 'header mb-2', textContent: 'Security' });
		this.logoutInformation = Alert.make(
			'info',
			`This will log you out of all active Sessions on other devices except this one.`
		);
		this.logoutButton = DOM.button('error', 'x-circle', 'Logout of all Sessions');
		this.logoutButton.classList.add('mt-2');
		this.logoutWrapper = DOM.create('div', { className: 'text-center', childs: [this.logoutButton] });
	}

	bind(): void {
		this.form.addEventListener('submit', async (event) => {
			event.preventDefault();
			this.runOnce(
				this.form,
				async () => {
					// We need to check each fields individually since they are not all required
					const username = this.username.value.trim();
					if (username != '' && !this.validators.user.validate()) {
						return;
					}
					const email = this.email.value.trim();
					if (email != '' && !this.validators.email.validate()) {
						return;
					}
					const password = this.password.value.trim();
					if (
						password != '' &&
						(!this.validators.password.validate() || !this.validators.confirmPassword.validate())
					) {
						return;
					}
					if (!this.validators.currentPassword.validate()) {
						return;
					}
					// Construct
					const body: Partial<Pick<User, 'username' | 'email' | 'verified' | 'receiveComments'>> & {
						password?: string;
						currentPassword: string;
					} = { currentPassword: this.currentPassword.value.trim() };
					if (username != this.application.auth.user.username) {
						body.username = username;
					}
					if (email != this.application.auth.user.email) {
						body.email = email;
					}
					if (password != '') {
						body.password = password;
					}
					if (this.receiveCommentsToggle.checkbox.checked != this.application.auth.user.receiveComments) {
						body.receiveComments = this.receiveCommentsToggle.checkbox.checked;
					}
					// Send the request
					const response = await Http.patch<{ success: string; verified: boolean }>(
						'/api/account/update',
						body
					);
					if (response.ok) {
						this.application.auth.user.username = this.username.value;
						this.application.auth.user.email = this.email.value;
						this.application.auth.user.receiveComments = this.receiveCommentsToggle.checkbox.checked;
						this.application.auth.user.verified = response.body.verified;
						this.password.value = '';
						this.confirmPassword.value = '';
						this.currentPassword.value = '';
						// Update the navigation now
						this.application.navigation.render();
						Notification.show('success', response.body.success);
					} else {
						Notification.show('danger', `Error: ${response.body.error}`);
					}
				},
				[this.username, this.email, this.password, this.confirmPassword, this.currentPassword, this.submit]
			);
		});
		this.logoutButton.addEventListener('click', async (event) => {
			event.preventDefault();
			if (!this.logoutButton.disabled) {
				this.logoutButton.disabled = true;
				const response = await Http.delete<{ success: string }>('/api/logout/all');
				if (response.ok) {
					Notification.show('success', response.body.success);
				} else {
					Notification.show('danger', `Error: ${response.body.error}`);
				}
				this.logoutButton.disabled = false;
			}
		});
	}

	render(): void {
		this.username.value = this.application.auth.user.username;
		this.email.value = this.application.auth.user.email;
		this.receiveCommentsToggle.checkbox.checked = this.application.auth.user.receiveComments;
		DOM.append(this.parent, this.header, this.form, this.logoutHeader, this.logoutInformation, this.logoutWrapper);
	}
}
