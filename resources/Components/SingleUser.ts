import { Component } from '../Component';
import { DOM } from '../Utility/DOM';

export class SingleUser extends Component {
	id: number = 0;
	header!: HTMLElement;
	grid!: HTMLElement;
	cards!: HTMLElement[];

	create(): void {
		this.header = DOM.create('h1', { className: 'header', textContent: '# User' });
		this.grid = DOM.create('div', {
			className: 'grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-2 auto-rows-min justify-items-center p-4',
		});
	}

	async data(params: RegExpMatchArray) {
		const id = parseInt(params[1]);
		if (!isNaN(id) && id > 0) {
			this.id = id;
		}
	}

	bind(): void {}

	render(): void {
		if (this.id < 1) {
			DOM.append(
				this.parent,
				DOM.create('h1', { className: 'text-center text-6xl', textContent: '404' }),
				DOM.create('h2', { className: 'text-center text-4xl', textContent: 'User not Found' }),
				DOM.create('div', { className: 'text-center', textContent: 'How did you get there ?' })
			);
			return;
		}
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
