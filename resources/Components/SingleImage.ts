import { Component } from '../Component';
import { Alert } from '../UI/Alert';
import { Badge } from '../UI/Badge';
import { Notification } from '../UI/Notification';
import { DOM } from '../Utility/DOM';
import { Http, InvalidHttpResponse } from '../Utility/Http';
import { Validator } from '../Utility/Validator';

type SingleImageResponse = {
	image: ImageModel;
	user: PublicUser;
	likes: number;
	liked: boolean;
	comments: ImageComment[];
};

export class SingleImage extends Component {
	id: number = 0;

	header!: HTMLElement;
	imageSlot!: HTMLImageElement;
	videoSlot!: HTMLVideoElement;
	stats!: HTMLElement;
	author!: HTMLAnchorElement;
	authorBadge!: HTMLElement;
	likes!: HTMLElement;
	likeIcon!: SVGSVGElement;
	likeCount!: HTMLElement;
	comments!: HTMLElement;
	commentCount!: HTMLElement;
	delete!: HTMLButtonElement;
	form!: HTMLFormElement;
	commentLabel!: HTMLLabelElement;
	inputWrapper!: HTMLElement;
	comment!: HTMLInputElement;
	submit!: HTMLButtonElement;
	commentList!: HTMLElement;

	response: SingleImageResponse | undefined;
	dataError: InvalidHttpResponse<{ error: string }> | undefined;

	create(): void {
		this.header = DOM.create('h1', { className: 'header', textContent: '#' });
		this.imageSlot = DOM.create('img', { className: 'm-auto shadow-md' });
		this.videoSlot = DOM.create('video', { className: 'm-auto shadow-md', autoplay: true, loop: true, volume: 0 });
		this.author = DOM.create('a', { className: 'author' });
		this.authorBadge = DOM.create('span');
		this.likeCount = DOM.create('span', { textContent: '123' });
		this.likeIcon = DOM.icon('heart', { classes: 'like', width: 'w-10', height: 'h-10' });
		this.likes = DOM.create('div', {
			className: 'text-center cursor-pointer',
			childs: [this.likeIcon, this.likeCount],
		});
		this.commentCount = DOM.create('span', { textContent: '123' });
		this.comments = DOM.create('div', {
			className: 'text-center cursor-pointer',
			childs: [DOM.icon('chat', { classes: 'text-gray-400', width: 'w-10', height: 'h-10' }), this.commentCount],
		});
		this.delete = DOM.button('error', 'x-circle', 'Delete');
		this.stats = DOM.create('div', {
			className: 'flex flex-row flex-nowrap justify-evenly mt-2 items-center',
			childs: [
				DOM.create('div', { childs: [DOM.text('Created by '), this.author, this.authorBadge] }),
				this.likes,
				this.comments,
				this.delete,
			],
		});
		this.commentLabel = DOM.create('label', { htmlFor: 'comment-message', textContent: 'Comment' });
		this.comment = DOM.create('input', {
			className: 'flex-grow mb-0 rounded-tr-none rounded-br-none border-r-0',
			type: 'text',
			id: 'comment-message',
			placeholder: 'Say what you think !',
		});
		this.submit = DOM.button('primary', 'chat');
		this.submit.classList.add('flex-shrink-0', 'rounded-tl-none', 'rounded-bl-none');
		this.inputWrapper = DOM.create('div', {
			className: 'flex flex-row flex-nowrap items-stretch',
			childs: [this.comment, this.submit],
		});
		this.form = DOM.create('form', {
			className: 'flex flex-col flex-wrap items-stretch',
			childs: [this.commentLabel, this.inputWrapper],
		});
		this.commentList = DOM.create('div', { className: 'flex flex-col flex-wrap' });
		this.validators.comment = new Validator(this.comment, (value: string) => {
			return value.length < 1 ? 'Your comment need to be at least 1 character long.' : true;
		});
	}

	async data(params: RegExpMatchArray) {
		const id = parseInt(params[1]);
		if (!isNaN(id) && id > 0) {
			this.id = id;
		}
		const response = await Http.get<SingleImageResponse>(`/api/${this.id}`);
		if (response.ok) {
			this.response = response.body;
		} else {
			this.dataError = response;
		}
	}

	bind(): void {
		this.author.addEventListener('click', (event) => {
			event.preventDefault();
			if (!this.response) return;
			this.application.navigate(`/user/${this.response.user.id}`);
		});
		this.likes.addEventListener('click', async (event) => {
			event.preventDefault();
			this.runOnce(this.likes, async () => {
				if (!this.response) return;
				if (!this.application.auth.loggedIn) {
					Notification.show('info', 'You need to be logged in to leave a like.');
					return;
				}
				if (this.application.auth.user.id == this.response.user.id) {
					Notification.show('info', `You can't like your own Image.`);
					return;
				}
				const response = await Http.put<{ success: string; total: number; liked: boolean }>(
					`/api/${this.id}/like`
				);
				if (response.ok) {
					this.likeIcon.classList.remove('active');
					if (response.body.liked) {
						this.likeIcon.classList.add('active');
					}
					this.likeCount.textContent = `${response.body.total}`;
					Notification.show('success', response.body.success);
				} else {
					Notification.show('danger', response.body.error);
					if (response.status == 404) {
						this.application.navigate('/');
					}
				}
			});
		});
		this.comments.addEventListener('click', (event) => {
			event.preventDefault();
			this.comment.focus();
		});
		this.delete.addEventListener('click', async (event) => {
			if (
				this.response &&
				this.application.auth.loggedIn &&
				this.response.user.id == this.application.auth.user.id
			) {
				await this.runOnce(
					this.delete,
					async () => {
						const response = await Http.delete<{ success: string }>(`/api/${this.response!.image.id}`);
						if (response.ok) {
							Notification.show('success', response.body.success);
						} else {
							Notification.show('danger', response.body.error);
						}
					},
					[this.delete, this.comment, this.submit]
				);
				this.application.navigate('/');
			}
		});
		this.form.addEventListener('submit', async (event) => {
			event.preventDefault();
			this.runOnce(
				this.likes,
				async () => {
					if (!this.application.auth.loggedIn) {
						Notification.show('info', 'You need to be logged in to leave a comment.');
						return;
					}
					if (!this.response) return;
					if (!this.validate()) return;
					const comment = this.comment.value.trim();
					const response = await Http.post<{ success: string; id: number }>(`/api/${this.id}/comment`, {
						message: comment,
					});
					if (response.ok) {
						this.response.comments.unshift({
							id: response.body.id,
							message: comment,
							user: this.application.auth.user,
							at: new Date().toISOString(),
						});
						this.renderComments(this.response.comments);
						this.comment.value = '';
						Notification.show('success', response.body.success);
					} else {
						Notification.show('danger', response.body.error);
						if (response.status == 404) {
							this.application.navigate('/');
						}
					}
				},
				[this.comment, this.submit]
			);
		});
	}

	renderComments(comments: ImageComment[]): void {
		this.commentCount.textContent = `${comments.length}`;
		DOM.clear(this.commentList);
		if (comments.length == 0) {
			const alert = Alert.make('info', 'No comments yet, post the first one !');
			alert.classList.add('mt-2');
			this.commentList.appendChild(alert);
		} else {
			for (const comment of comments) {
				const cleanDate = new Date(comment.at).toLocaleString();
				const userLink = DOM.create('a', {
					href: `/user/${comment.user.id}`,
					childs: [DOM.text(comment.user.username), Badge.make(comment.user.verified, true)],
				});
				userLink.addEventListener('click', (event) => {
					event.preventDefault();
					this.application.navigate(`/user/${comment.user.id}`);
				});
				const node = DOM.create('div', {
					className: 'comment',
					childs: [
						DOM.create('p', {
							className: 'break-words',
							textContent: comment.message,
						}),
						DOM.create('div', {
							className: 'footer',
							childs: [userLink, DOM.text(`${cleanDate}`)],
						}),
					],
				});
				this.commentList.appendChild(node);
			}
		}
	}

	render(): void {
		// Handle route error
		if (this.id < 1) {
			this.genericError('404', 'Image not Found', 'How did you get there ?');
			return;
		}
		// Handle API error
		if (this.dataError) {
			this.genericError(`${this.dataError.status}`, 'Error', this.dataError.body.error);
			return;
		}
		// Display
		if (!this.response) return;
		if (!this.application.auth.loggedIn || this.response.user.id != this.application.auth.user.id) {
			this.delete.remove();
		}
		this.header.textContent = `# ${this.response.image.id}`;
		this.author.textContent = this.response.user.username;
		this.author.href = `/user/${this.response.user.id}`;
		Badge.set(this.authorBadge, this.response.user.verified);
		const media = `/uploads/${this.response.image.name}`;
		let display: HTMLElement;
		if (this.response.image.animated) {
			display = this.videoSlot;
			this.videoSlot.src = media;
		} else {
			display = this.imageSlot;
			this.imageSlot.src = media;
		}
		this.likeCount.textContent = `${this.response.likes}`;
		if (
			this.response.liked ||
			(this.application.auth.loggedIn && this.response.user.id == this.application.auth.user.id)
		) {
			this.likeIcon.classList.add('active');
		}
		DOM.append(this.parent, this.header, display, this.stats);
		if (this.application.auth.loggedIn && this.application.auth.user.verified) {
			this.parent.appendChild(this.form);
		} else {
			this.parent.appendChild(Alert.make('info', `You need to be logged in and verified to post a comment.`));
		}
		this.renderComments(this.response.comments);
		this.parent.appendChild(this.commentList);
	}
}
