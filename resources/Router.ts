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

	match(location: string): { route: Route; params: RegExpMatchArray } | undefined {
		for (const route of this.routes) {
			const match = location.match(route.path);
			if (match) {
				return { route, params: match };
			}
		}
		return undefined;
	}
}
