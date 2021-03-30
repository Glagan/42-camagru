import { Component } from './Component';

export interface Route {
	path: string;
	component: typeof Component;
}

export interface RouteMatch {
	route: Route;
	params: RegExpMatchArray;
	query: Record<string, string>;
}

export class Router {
	routes: Route[];

	constructor() {
		this.routes = [];
	}

	add(path: string, component: typeof Component): void {
		this.routes.push({ path, component });
	}

	match(location: string, query?: string): RouteMatch | undefined {
		for (const route of this.routes) {
			const match = location.match(route.path);
			if (match) {
				const routeQuery: Record<string, string> = {};
				if (query) {
					const search = query.substr(1).split('&');
					for (const query of search) {
						const [name, value] = query.split('=');
						if (name && value) {
							routeQuery[name] = value;
						}
					}
				}
				return { route, params: match, query: routeQuery };
			}
		}
		return undefined;
	}
}
