import { Application } from '../Application';
import { DOM } from '../Utility/DOM';
import { Http } from '../Utility/Http';
import { Observer } from '../Utility/Observer';
import { Alert } from './Alert';
import { LOADING_IMG, LOADING_VIDEO } from './Loading';
import { Notification } from './Notification';

export class ImageList {
	application: Application;
	grid: HTMLElement;
	images: ImageModel[];
	page: number = 1;
	url: string;
	lastPage: boolean = false;

	constructor(application: Application, url: string) {
		this.application = application;
		this.grid = DOM.create('div', {
			className: 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 auto-rows-min justify-items-center items-center',
		});
		this.url = url;
		this.images = [];
	}

	private displayList(list: ImageModel[]): void {
		const observer = Observer.get(async (img: HTMLElement) => {
			if (img.dataset.last && !this.lastPage) {
				this.page++;
				const response = await Http.get<{ images: ImageModel[] }>(`${this.url}/${this.page}`);
				if (response.ok) {
					const images = response.body.images;
					if (images.length < 10) {
						this.lastPage = true;
					}
					this.displayList(images);
				} else {
					Notification.show('danger', response.body.error);
				}
			}
		});
		const length = list.length;
		for (let i = 0; i < length; i++) {
			const image = list[i];
			const display = DOM.create(image.animated ? 'video' : 'img', {
				src: image.animated ? LOADING_VIDEO : LOADING_IMG,
				dataset: { src: `/uploads/${image.name}` },
				autoplay: true,
				loop: true,
				volume: 0,
			});
			if (i == length - 1) {
				display.dataset.last = 'true';
			}
			observer.observe(display);
			const link = DOM.create('a', { href: `/${image.id}`, childs: [display] });
			const card = DOM.create('div', { className: 'card', childs: [link] });
			link.addEventListener('click', (event) => {
				event.preventDefault();
				this.application.navigate(`/${image.id}`);
			});
			this.grid.appendChild(card);
		}
	}

	render(): void {
		if (this.images.length == 0) {
			const alert = Alert.make('info', 'No Images yet, post the first one !');
			alert.classList.add('mt-2', 'col-span-full', 'w-full');
			this.grid.appendChild(alert);
			return;
		} else this.displayList(this.images);
	}
}
