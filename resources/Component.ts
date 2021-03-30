import { Application } from './Application';
import { Validator } from './Utility/Validator';

export interface Component {
	bind?(): void;
	data?(params: RegExpMatchArray): Promise<void>;
}

export abstract class Component {
	application: Application;
	parent: HTMLElement;
	trigger: { [key: string]: (payload?: object) => void } = {};
	static auth: boolean | undefined = undefined;
	validators: { [key: string]: Validator } = {};

	constructor(application: Application, parent?: HTMLElement) {
		this.application = application;
		if (parent) {
			this.parent = parent;
		} else {
			this.parent = document.createElement('div');
		}
		this.create();
		if (this.bind) {
			this.bind();
		}
	}

	protected link(node: HTMLButtonElement, location: string): void {
		node.addEventListener('click', (event) => {
			event.preventDefault();
			this.application.navigate(location);
		});
	}

	abstract create(): void;
	abstract render(): void;

	validate(): boolean {
		for (const key in this.validators) {
			if (Object.prototype.hasOwnProperty.call(this.validators, key)) {
				const validator = this.validators[key];
				if (!validator.validate()) {
					return false;
				}
			}
		}
		return true;
	}
}
