import { Component } from '../Component';
import { Alert } from '../UI/Alert';
import { ImageList } from '../UI/ImageList';
import { Notification } from '../UI/Notification';
import { DOM } from '../Utility/DOM';
import { Http, InvalidHttpResponse } from '../Utility/Http';

export class List extends Component {
	header!: HTMLElement;
	imageList!: ImageList;
	dataError: InvalidHttpResponse<{ error: string }> | undefined;

	create(): void {
		this.header = DOM.create('h1', { className: 'header', textContent: '# All' });
		this.imageList = new ImageList(this.application, '/api/list');
	}

	async data() {
		const response = await Http.get<{ images: ImageModel[] }>('/api/list');
		if (response.ok) {
			this.imageList.images = response.body.images;
		} else {
			this.imageList.images = [];
			this.dataError = response;
			Notification.show('danger', response.body.error);
		}
	}

	render(): void {
		if (this.dataError) {
			// ?
			return;
		}
		this.imageList.render();
		DOM.append(this.parent, this.header, this.imageList.grid);
	}
}
