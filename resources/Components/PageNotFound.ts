import { Component } from '../Component';
import { DOM } from '../Utility/DOM';

export class PageNotFound extends Component {
	code!: HTMLElement;
	header!: HTMLElement;
	message!: HTMLElement;

	create(): void {
		this.code = DOM.create('h1', { className: 'text-center text-6xl', textContent: '404' });
		this.header = DOM.create('h2', { className: 'text-center text-4xl', textContent: 'Page not Found' });
		this.message = DOM.create('div', { className: 'text-center', textContent: 'How did you get there ?' });
	}

	bind(): void {}

	render(): void {
		DOM.append(this.parent, this.code, this.header, this.message);
	}
}
