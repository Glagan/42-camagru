import { Auth } from './Auth';
import { Component } from './Component';
import { Navigation } from './Components/Navigation';
import { PageNotFound } from './Components/PageNotFound';
import { Unauthorized } from './Components/Unauthorized';
import { Router, Route, RouteMatch } from './Router';
import { DOM } from './Utility/DOM';

export class Application {
	smallNavigation: HTMLElement;
	navigation: Navigation;
	main: HTMLElement;
	loading: HTMLElement;
	router: Router;
	auth: Auth;
	page: string;
	currentMatch: RouteMatch | undefined;
	currentComponent: Component | undefined;
	// Errors cache
	pageNotFound: PageNotFound;
	unauthorized: Unauthorized;

	constructor(router: Router) {
		this.auth = new Auth();
		this.auth.status(); // Preload session status
		this.smallNavigation = document.getElementById('small-navigation')!;
		this.smallNavigation.appendChild(DOM.icon('menu', { width: 'w-10', height: 'h-10' }));
		this.navigation = new Navigation(this, document.getElementById('navigation')!);
		this.smallNavigation.addEventListener('click', (event) => {
			event.preventDefault();
			this.navigation.parent.classList.toggle('active');
		});
		this.main = document.getElementById('content')!;
		this.loading = document.getElementById('loading')!;
		this.router = router;
		this.page = '';
		this.pageNotFound = new PageNotFound(this, this.main);
		this.unauthorized = new Unauthorized(this, this.main);
		window.addEventListener('popstate', (event) => {
			this.setLocation(event.state.location);
		});
	}

	private async refresh(): Promise<boolean> {
		if (this.currentMatch) {
			const component = await this.createComponent(this.currentMatch.route, this.currentMatch.params);
			this.renderComponent(component);
			return true;
		}
		return false;
	}

	loggedIn(user: User): void {
		this.auth.login(user);
		this.navigate('/');
	}

	loggedOut(): void {
		this.auth.logout();
		this.refresh();
	}

	private async createComponent(route: Route, params: RegExpMatchArray): Promise<Component> {
		const _class = route.component;
		if (_class.auth !== undefined) {
			const loggedIn = await this.auth.isLoggedIn;
			if ((_class.auth && !loggedIn) || (!_class.auth && loggedIn)) {
				return this.unauthorized;
			}
		}
		/// @ts-ignore route.component is NOT abstract
		const component: Component = new _class(this, this.main);
		if (component.data) {
			await component.data(params);
		}
		return component;
	}

	private renderComponent(component: Component) {
		DOM.clear(this.main);
		if (this.currentComponent && this.currentComponent.destroy) {
			this.currentComponent.destroy();
		}
		this.page = component.constructor.name;
		this.currentComponent = component;
		component.render();
		this.navigation.render();
	}

	async setLocation(location: string, query?: string): Promise<void> {
		const match = this.router.match(location, query);
		if (match === undefined) {
			this.currentMatch = undefined;
			this.renderComponent(this.pageNotFound);
			return;
		}
		this.currentMatch = match;
		DOM.clear(this.main);
		DOM.append(this.main, this.loading);
		const component = await this.createComponent(match.route, match.params);
		this.renderComponent(component);
	}

	async navigate(location: string, query?: string): Promise<void> {
		history.pushState({ location, query }, '', location);
		this.navigation.parent.classList.remove('active');
		this.setLocation(location, query);
	}
}
