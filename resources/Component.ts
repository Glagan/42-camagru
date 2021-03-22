import { Application } from './Application';

export interface Component {
	data?(): Promise<void>;
	created?(): void;
	render?(): void;
}

export abstract class Component {
	application: Application;
	parent: HTMLElement;
	trigger: { [key: string]: (payload?: object) => void } = {};

	constructor(application: Application, parent?: HTMLElement) {
		this.application = application;
		if (parent) {
			this.parent = parent;
		} else {
			this.parent = document.createElement('div');
		}
		if (this.created) this.created();
	}
}
