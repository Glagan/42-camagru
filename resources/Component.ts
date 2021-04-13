import { Application } from './Application';
import { DOM } from './Utility/DOM';
import { Validator } from './Utility/Validator';

export interface Component {
	bind?(): void;
	data?(params: RegExpMatchArray): Promise<void>;
	destroy?(): void;
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

	protected link(node: HTMLAnchorElement): void;
	protected link(node: HTMLElement, location?: string): void {
		node.addEventListener('click', (event) => {
			event.preventDefault();
			let path: string;
			// Find path from anchor
			if (node instanceof HTMLAnchorElement) {
				path = node.pathname;
			} else if (location) {
				path = location;
			} else {
				path = '/';
			}
			// Make sure that the path starts with /
			if (path[0] != '/') path = `/${path}`;
			this.application.navigate(path);
		});
	}

	abstract create(): void;
	abstract render(): void;

	genericError(title: string, header: string, content: string): void {
		DOM.append(
			this.parent,
			DOM.create('h1', { className: 'text-center text-6xl', textContent: title }),
			DOM.create('h2', { className: 'text-center text-4xl', textContent: header }),
			DOM.create('div', { className: 'text-center', textContent: content })
		);
		return;
	}

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

	async runOnce(
		node: HTMLElement,
		fct: () => Promise<void>,
		block?: (HTMLInputElement | HTMLButtonElement | HTMLAnchorElement)[]
	): Promise<boolean> {
		if (node.dataset.pending) return false;
		node.dataset.pending = 'true';
		if (block !== undefined) {
			for (const node of block) {
				if (node instanceof HTMLAnchorElement) {
					if (node.classList.contains('disabled')) {
						node.dataset.alreadyDisabled = 'true';
					} else {
						node.classList.add('disabled');
					}
				} else {
					if (node.disabled) {
						node.dataset.alreadyDisabled = 'true';
					} else {
						node.disabled = true;
					}
				}
			}
		}
		await fct();
		delete node.dataset.pending;
		if (block !== undefined) {
			for (const node of block) {
				if (node.dataset.alreadyDisabled) {
					delete node.dataset.alreadyDisabled;
				} else if (node instanceof HTMLAnchorElement) {
					node.classList.remove('disabled');
				} else {
					node.disabled = false;
				}
			}
		}
		return true;
	}
}
