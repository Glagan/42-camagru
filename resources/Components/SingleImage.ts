import { Component } from '../Component';
import { DOM } from '../Utility/DOM';

export class SingleImage extends Component {
	header!: HTMLElement;
	image!: HTMLImageElement;
	stats!: HTMLElement;
	likes!: HTMLElement;
	likeCount!: HTMLElement;
	comments!: HTMLElement;
	commentCount!: HTMLElement;
	form!: HTMLFormElement;
	commentLabel!: HTMLLabelElement;
	inputWrapper!: HTMLElement;
	comment!: HTMLInputElement;
	submit!: HTMLButtonElement;
	commentList!: HTMLElement;

	create(): void {
		this.header = DOM.create('h1', { className: 'header', textContent: '#' });
		this.image = DOM.create('img', {
			src: 'https://via.placeholder.com/900x450',
			className: 'shadow-md',
			width: 900,
			height: 450,
		});
		this.likeCount = DOM.create('span', { textContent: '123' });
		this.likes = DOM.create('div', {
			className: 'text-center cursor-pointer',
			childs: [DOM.icon('heart', { classes: 'like', width: 'w-10', height: 'h-10' }), this.likeCount],
		});
		this.commentCount = DOM.create('span', { textContent: '123' });
		this.comments = DOM.create('div', {
			className: 'text-center cursor-pointer',
			childs: [DOM.icon('chat', { classes: 'text-gray-400', width: 'w-10', height: 'h-10' }), this.commentCount],
		});
		this.stats = DOM.create('div', {
			className: 'flex flex-row flex-nowrap justify-evenly mt-2',
			childs: [this.likes, this.comments],
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
	}

	bind(): void {}

	render(): void {
		for (let index = 0; index < 10; index++) {
			const comment = DOM.create('div', {
				className: 'comment',
				childs: [
					DOM.create('p', {
						className: 'break-words',
						textContent:
							'Facere voluptatem omnis consectetur. Quia doloremque aliquam tempore dolorem at quidem vel. Omnis dolores at quia quisquam vel consequatur accusantium. Non nostrum fugit repudiandae laborum modi amet rem. Accusantium omnis voluptatibus sunt.',
					}),
					DOM.create('div', { className: 'footer', textContent: 'Anonymous - 22 March 2021 at 15:03' }),
				],
			});
			this.commentList.appendChild(comment);
		}
		DOM.append(this.parent, this.header, this.image, this.stats, this.form, this.commentList);
	}
}
