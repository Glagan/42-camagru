import { Auth } from '../Auth';
import { Component } from '../Component';
import { Badge } from '../UI/Badge';
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
	createButton!: HTMLAnchorElement;
	verify!: HTMLAnchorElement;
	login!: HTMLAnchorElement;
	register!: HTMLAnchorElement;
	back!: HTMLAnchorElement;
	selfImages!: HTMLAnchorElement;
	account!: HTMLAnchorElement;
	preferences!: HTMLAnchorElement;
	logout!: HTMLAnchorElement;

	create() {
		this.title = DOM.create('h1', {
			className: 'text-6xl md:text-4xl xl:text-6xl pb-4',
			textContent: 'camagru',
		});
		this.user = DOM.create('div', { className: 'mb-2' });
		this.warningBadge = Badge.make(false);
		this.verifiedBadge = Badge.make(true);
		this.links = DOM.create('div', {
			className: 'links flex flex-col flex-wrap items-center',
		});
		this.createButton = DOM.link('primary', 'camera', 'Create', '/create');
		this.verify = DOM.link('success', 'at-symbol', 'Verify Account', '/verify');
		this.login = DOM.link('success', 'login', 'Login', '/login');
		this.register = DOM.link('success', 'user-add', 'Register', '/register');
		this.account = DOM.link('secondary', 'user', 'Account', '/account');
		this.preferences = DOM.link('secondary', 'cog', 'Preferences', '/preferences');
		this.logout = DOM.link('error', 'logout', 'Logout', '/');
		this.back = DOM.link('secondary', 'chevron-left', 'Home', '/');
		this.selfImages = DOM.link('secondary', 'photograph', 'My Images', '/');
	}

	bind() {
		this.link(this.createButton);
		this.link(this.verify);
		this.link(this.login);
		this.link(this.register);
		this.link(this.account);
		this.link(this.preferences);
		this.link(this.back);
		this.link(this.selfImages);
		this.logout.addEventListener('click', async (event) => {
			event.preventDefault();
			await Http.delete('/api/logout');
			// Always refresh Components
			this.application.loggedOut();
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
		this.selfImages.href = `/user/${this.application.auth.user.id}`;
		if (authUser.verified) {
			this.user.appendChild(this.verifiedBadge);
			this.createButton.title = '';
			this.createButton.classList.remove('disabled');
		} else {
			this.user.appendChild(this.warningBadge);
			this.createButton.title = 'You need to Verify your account to create Images !';
			this.createButton.classList.add('disabled');
		}
	}

	render() {
		const auth = this.application.auth;
		DOM.clear(this.user);
		DOM.clear(this.links);
		if (auth.loggedIn) {
			this.displayLoggedInUser();
			if (!auth.user.verified) {
				DOM.append(this.links, this.verify);
			}
			if (this.application.page !== Create.name) {
				this.links.appendChild(this.createButton);
			}
			if (this.application.page !== List.name) {
				this.links.appendChild(this.back);
			}
			DOM.append(this.links, this.selfImages, this.account, this.preferences, this.logout);
		} else {
			if (this.application.page !== List.name) {
				this.links.appendChild(this.back);
			}
			DOM.append(this.links, this.login, this.register, this.preferences);
		}
		DOM.append(this.parent, this.title, this.user, this.links);
	}
}
