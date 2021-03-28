import { Component } from '../Component';
import { DOM } from '../Utility/DOM';

export class Unauthorized extends Component {
	code!: HTMLElement;
	header!: HTMLElement;
	message!: HTMLElement;

	create(): void {
		this.code = DOM.create('h1', { className: 'text-center text-6xl', textContent: '401' });
		this.header = DOM.create('h2', { className: 'text-center text-4xl', textContent: 'Unauthorized' });
		this.message = DOM.create('div', { className: 'text-center', textContent: "You shouldn't be here !" });
	}

	render(): void {
		DOM.append(this.parent, this.code, this.header, this.message);
	}
}
