import { Component } from '../Component';
import { DOM } from '../Utility/DOM';

export class PageNotFound extends Component {
	header!: HTMLElement;
	message!: HTMLElement;

	create(): void {
		this.header = DOM.create('h1', { className: 'text-center text-6xl', textContent: '404' });
		this.message = DOM.create('div', { className: 'text-center', textContent: 'How did you get there ?' });
	}

	bind(): void {}

	render(): void {
		DOM.append(this.parent, this.header, this.message);
	}
}
