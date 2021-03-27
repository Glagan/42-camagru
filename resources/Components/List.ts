import { Component } from '../Component';
import { Alert } from '../UI/Alert';
import { Notification } from '../UI/Notification';
import { DOM } from '../Utility/DOM';
import { Http, InvalidHttpResponse } from '../Utility/Http';

export class List extends Component {
	header!: HTMLElement;
	grid!: HTMLElement;

	list: ImageModel[] = [];
	dataError: InvalidHttpResponse<{ error: string }> | undefined;

	create(): void {
		this.header = DOM.create('h1', { className: 'header', textContent: '# All' });
		this.grid = DOM.create('div', {
			className: 'grid grid-cols-2 lg:grid-cols-3 auto-rows-min justify-items-center items-center',
		});
	}

	async data() {
		const response = await Http.get<{ images: ImageModel[] }>('/api/list');
		if (response.ok) {
			this.list = response.body.images;
		} else {
			this.list = [];
			this.dataError = response;
			Notification.show('danger', response.body.error);
		}
	}

	bind(): void {}

	render(): void {
		if (this.dataError) {
			// ...
			return;
		}
		if (this.list.length == 0) {
			const alert = Alert.make('info', 'No comments yet, post the first one !');
			alert.classList.add('mt-2');
			this.grid.appendChild(alert);
			return;
		} else {
			for (const image of this.list) {
				const card = DOM.create('div', {
					className: 'card',
					childs: [DOM.create('img', { src: `/uploads/${image.id}` })],
				});
				card.addEventListener('click', (event) => {
					event.preventDefault();
					this.application.navigate(`/${image.id}`);
				});
				this.grid.appendChild(card);
			}
		}
		DOM.append(this.parent, this.header, this.grid);
	}
}
