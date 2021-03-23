import { Application } from './Application';

export interface Component {
	data?(): Promise<void>;
	destroy?(): void;
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
		this.create();
		this.bind();
	}

	abstract create(): void;
	abstract bind(): void;
	abstract render(): void;
}
