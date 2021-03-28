import { Application } from '../Application';
import { DOM } from '../Utility/DOM';
import { Alert } from './Alert';

export class ImageList {
	application: Application;
	grid: HTMLElement;
	images: ImageModel[];

	constructor(application: Application, images: ImageModel[]) {
		this.application = application;
		this.grid = DOM.create('div', {
			className: 'grid grid-cols-2 lg:grid-cols-3 auto-rows-min justify-items-center items-center',
		});
		this.images = images;
	}

	render(): void {
		if (this.images.length == 0) {
			const alert = Alert.make('info', 'No Images yet, post the first one !');
			alert.classList.add('mt-2', 'col-span-full', 'w-full');
			this.grid.appendChild(alert);
			return;
		} else {
			for (const image of this.images) {
				const link = DOM.create('a', {
					href: `/${image.id}`,
					childs: [DOM.create('img', { src: `/uploads/${image.id}` })],
				});
				const card = DOM.create('div', {
					className: 'card',
					childs: [link],
				});
				link.addEventListener('click', (event) => {
					event.preventDefault();
					this.application.navigate(`/${image.id}`);
				});
				this.grid.appendChild(card);
			}
		}
	}
}
