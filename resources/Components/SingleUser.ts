import { Component } from '../Component';
import { DOM } from '../Utility/DOM';
import { Http, InvalidHttpResponse } from '../Utility/Http';
import { ImageList } from '../UI/ImageList';
import { Notification } from '../UI/Notification';

export class SingleUser extends Component {
	id: number = 0;

	header!: HTMLElement;
	user!: PublicUser;
	imageList!: ImageList;
	dataError: InvalidHttpResponse<{ error: string }> | undefined;

	create(): void {
		this.header = DOM.create('h1', { className: 'header', textContent: '# All' });
		this.imageList = new ImageList(this.application, '');
	}

	async data(params: RegExpMatchArray) {
		const id = parseInt(params[1]);
		if (!isNaN(id) && id > 0) {
			this.id = id;
			this.imageList.url = `/api/user/${this.id}`;
			const response = await Http.get<{ user: PublicUser; images: ImageModel[] }>(`/api/user/${this.id}`);
			if (response.ok) {
				this.user = response.body.user;
				this.imageList.images = response.body.images;
			} else {
				this.imageList.images = [];
				this.dataError = response;
				Notification.show('danger', response.body.error);
			}
		} else {
			this.dataError = { ok: false, headers: {}, status: 400, body: { error: 'Invalid User ID.' } };
		}
	}

	render(): void {
		if (this.dataError) {
			this.genericError(`${this.dataError.status}`, 'Error', this.dataError.body.error);
			return;
		}
		this.header.textContent = `@ ${this.user.username}`;
		this.imageList.render();
		DOM.append(this.parent, this.header, this.imageList.grid);
	}
}
