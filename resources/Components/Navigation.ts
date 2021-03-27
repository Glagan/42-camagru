import { Auth } from '../Auth';
import { Component } from '../Component';
import { Notification } from '../UI/Notification';
import { DOM } from '../Utility/DOM';
import { Http } from '../Utility/Http';
import { Create } from './Create';
import { List } from './List';

export class Navigation extends Component {
	title!: HTMLElement;
	user!: HTMLElement;
	warningBadge!: HTMLElement;
	verifiedBadge!: HTMLElement;
	links!: HTMLElement;
	createButton!: HTMLButtonElement;
	sendVerification!: HTMLButtonElement;
	login!: HTMLButtonElement;
	register!: HTMLButtonElement;
	back!: HTMLButtonElement;
	account!: HTMLButtonElement;
	preferences!: HTMLButtonElement;
	logout!: HTMLButtonElement;

	create() {
		this.parent.className = 'flex-1 text-center p-4 h-auto md:h-screen relative md:sticky top-0 w-full md:w-4/12';
		this.title = DOM.create('h1', {
			className: 'text-6xl pb-4',
			textContent: 'camagru',
		});
		this.user = DOM.create('div', { className: 'mb-2' });
		this.warningBadge = DOM.create('span', {
			className: 'badge warning',
			childs: [DOM.icon('exclamation', { width: 'w-5', height: 'h-5' })],
			title: 'Not Verified',
		});
		this.verifiedBadge = DOM.create('span', {
			className: 'badge success',
			childs: [DOM.icon('check', { width: 'w-5', height: 'h-5' })],
			title: 'Verified',
		});
		this.links = DOM.create('div', {
			className: 'links flex flex-col flex-wrap items-center',
		});
		this.createButton = DOM.button('primary', 'camera', 'Create');
		this.sendVerification = DOM.button('success', 'at-symbol', 'Send Verification');
		this.login = DOM.button('success', 'login', 'Login');
		this.register = DOM.button('success', 'user-add', 'Register');
		this.account = DOM.button('secondary', 'user', 'Account');
		this.preferences = DOM.button('secondary', 'cog', 'Preferences');
		this.logout = DOM.button('error', 'logout', 'Logout');
		this.back = DOM.button('secondary', 'chevron-left', 'Back Home');
	}

	bind() {
		this.sendVerification.addEventListener('click', async (event) => {
			event.preventDefault();
			const response = await Http.patch<{ success: string }>('/api/send-verification');
			if (response.ok) {
				Notification.show('success', response.body.success);
			} else {
				Notification.show('danger', response.body.error);
			}
		});
		this.link(this.createButton, '/create');
		this.link(this.login, '/login');
		this.link(this.register, '/register');
		this.link(this.account, '/account');
		this.link(this.preferences, '/preferences');
		this.link(this.back, '/');
		this.logout.addEventListener('click', async (event) => {
			event.preventDefault();
			const response = await Http.delete('/api/logout');
			if (response.ok) {
				this.application.loggedOut();
			}
		});
	}

	async data(_params: RegExpMatchArray) {
		if (this.application.auth.pendingStatus) {
			await this.application.auth.pendingStatus;
		}
	}

	private displayLoggedInUser() {
		const authUser = this.application.auth.user;
		this.user.textContent = authUser.username;
		if (authUser.verified) {
			this.user.appendChild(this.verifiedBadge);
		} else {
			this.user.appendChild(this.warningBadge);
		}
	}

	render() {
		const auth = this.application.auth;
		DOM.clear(this.user);
		DOM.clear(this.links);
		if (auth.loggedIn) {
			this.displayLoggedInUser();
			if (!auth.user.verified) {
				DOM.append(this.links, this.sendVerification);
			}
			if (this.application.page !== Create.name) {
				this.links.appendChild(this.createButton);
			}
			if (this.application.page !== List.name) {
				this.links.appendChild(this.back);
			}
			DOM.append(this.links, this.account, this.preferences, this.logout);
		} else {
			if (this.application.page !== List.name) {
				this.links.appendChild(this.back);
			}
			DOM.append(this.links, this.login, this.register, this.preferences);
		}
		DOM.append(this.parent, this.title, this.user, this.links);
	}
}
