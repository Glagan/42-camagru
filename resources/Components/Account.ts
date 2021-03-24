import { Component } from '../Component';
import { Alert } from '../UI/Alert';
import { Toggle } from '../UI/Toggle';
import { DOM } from '../Utility/DOM';

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
	labelCurrentPassword!: HTMLLabelElement;
	currentPassword!: HTMLInputElement;
	receiveCommentsToggle!: { label: HTMLLabelElement; checkbox: HTMLInputElement };
	footer!: HTMLElement;
	submit!: HTMLButtonElement;

	create(): void {
		this.header = DOM.create('h1', { className: 'header', textContent: 'Account' });
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
				this.receiveCommentsToggle.label,
				this.labelCurrentPassword,
				this.currentPassword,
				this.footer,
			],
		});
	}

	bind(): void {}

	render(): void {
		DOM.append(this.parent, this.header, this.form);
	}
}
