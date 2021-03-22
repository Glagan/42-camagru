import { Auth } from './Auth';
import { Component } from './Component';

export class Application {
	navigationNode: HTMLElement;
	contentNode: HTMLElement;
	components: Component[];
	activeComponents: Component[];
	auth: Auth;
	page: string;

	constructor() {
		this.navigationNode = document.getElementById('nav')!;
		this.contentNode = document.getElementById('content')!;
		this.auth = new Auth();
		this.auth.status(); // Preload session status
		// Load each components
		this.page = '';
		this.components = [];
		this.activeComponents = [];
	}

	dispatch(event: string, payload?: object) {
		for (const component of this.activeComponents) {
			if (event in component.trigger) {
				component.trigger[event](payload);
			}
		}
	}
}
