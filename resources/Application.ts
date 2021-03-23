import { Auth } from './Auth';
import { Component } from './Component';
import { Navigation } from './Components/Navigation';
import { PageNotFound } from './Components/PageNotFound';
import { Router } from './Router';
import { DOM } from './Utility/DOM';

export class Application {
	navigation: Navigation;
	main: HTMLElement;
	loading: HTMLElement;
	router: Router;
	auth: Auth;
	page: string;
	currentComponent: Component | undefined;

	constructor(router: Router) {
		this.auth = new Auth();
		this.auth.status(); // Preload session status
		this.navigation = new Navigation(this, document.getElementById('nav')!);
		this.main = document.getElementById('content')!;
		this.loading = DOM.create('div');
		this.router = router;
		this.page = '';
	}

	private renderComponent(component: Component) {
		DOM.clear(this.main);
		this.currentComponent = component;
		this.page = component.constructor.name;
		component.render();
		this.navigation.render();
	}

	async navigate(location: string): Promise<void> {
		const route = this.router.match(location);
		if (route === undefined) {
			const notFound = new PageNotFound(this);
			this.renderComponent(notFound);
			return;
		}
		// TODO: Loading
		const _class = route.component;
		/// @ts-ignore route.component is NOT abstract
		const component: Component = new _class(this, this.main);
		if (component.data) {
			await component.data();
		}
		// Destroy previous Component
		if (this.currentComponent && this.currentComponent.destroy) {
			this.currentComponent.destroy();
		}
		// Clear and render
		this.renderComponent(component);
	}
}
