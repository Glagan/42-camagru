import { Component } from '../Component';
import { DOM } from '../Utility/DOM';

export class List extends Component {
	header!: HTMLElement;
	grid!: HTMLElement;
	cards!: HTMLElement[];

	create(): void {
		this.header = DOM.create('h1', { className: 'header', textContent: '# All' });
		this.grid = DOM.create('div', {
			className: 'grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-2 auto-rows-min justify-items-center p-4',
		});
	}

	bind(): void {}

	render(): void {
		for (let index = 0; index < 10; index++) {
			const card = DOM.create('div', {
				className: 'card',
				childs: [DOM.create('img', { src: 'https://via.placeholder.com/150', width: 150, height: 150 })],
			});
			card.addEventListener('click', (event) => {
				event.preventDefault();
				this.application.navigate('/1');
			});
			this.grid.appendChild(card);
		}
		DOM.append(this.parent, this.header, this.grid);
	}
}
