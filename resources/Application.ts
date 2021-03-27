import { Auth } from './Auth';
import { Component } from './Component';
import { Navigation } from './Components/Navigation';
import { PageNotFound } from './Components/PageNotFound';
import { Unauthorized } from './Components/Unauthorized';
import { Router } from './Router';
import { DOM } from './Utility/DOM';
import { Theme } from './Utility/Theme';

export class Application {
	navigation: Navigation;
	main: HTMLElement;
	loading: HTMLElement;
	router: Router;
	auth: Auth;
	page: string;
	currentComponent: Component | undefined;
	// Errors cache
	pageNotFound: PageNotFound;
	unauthorized: Unauthorized;

	constructor(router: Router) {
		this.auth = new Auth();
		this.auth.status(); // Preload session status
		this.navigation = new Navigation(this, document.getElementById('nav')!);
		this.main = document.getElementById('content')!;
		this.loading = DOM.create('div');
		this.router = router;
		this.page = '';
		this.pageNotFound = new PageNotFound(this, this.main);
		this.unauthorized = new Unauthorized(this, this.main);
	}

	loggedIn(user: User): void {
		this.auth.login(user);
		Theme.set(user.theme);
		this.navigate('/');
	}

	loggedOut(): void {
		this.auth.logout();
		this.navigate('/');
	}

	private renderComponent(component: Component) {
		DOM.clear(this.main);
		if (this.currentComponent && this.currentComponent.destroy) {
			this.currentComponent.destroy();
		}
		this.currentComponent = component;
		this.page = component.constructor.name;
		component.render();
		this.navigation.render();
	}

	async navigate(location: string): Promise<void> {
		console.log('navigating to', location);
		const match = this.router.match(location);
		console.log('found route', match);
		if (match === undefined) {
			this.renderComponent(this.pageNotFound);
			return;
		}
		const { route, params } = match;
		// TODO: Loading
		const _class = route.component;
		if (_class.auth !== undefined) {
			const loggedIn = await this.auth.isLoggedIn;
			if ((_class.auth && !loggedIn) || (!_class.auth && loggedIn)) {
				this.renderComponent(this.unauthorized);
				return;
			}
		}
		/// @ts-ignore route.component is NOT abstract
		const component: Component = new _class(this, this.main);
		if (component.data) {
			await component.data(params);
		}
		// Clear and render
		this.renderComponent(component);
	}
}
