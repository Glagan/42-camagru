import { Component } from './Component';

interface Route {
	path: string;
	component: typeof Component;
}

export class Router {
	routes: Route[];

	constructor() {
		this.routes = [];
	}

	add(path: string, component: typeof Component): void {
		this.routes.push({ path, component });
	}

	match(location: string): Route | undefined {
		for (const route of this.routes) {
			if (route.path.match(location)) {
				return route;
			}
		}
		return undefined;
	}
}
