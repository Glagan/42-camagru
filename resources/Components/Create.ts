import { Component } from '../Component';
import { Alert } from '../UI/Alert';
import { Notification } from '../UI/Notification';
import { DOM } from '../Utility/DOM';
import { Http } from '../Utility/Http';

export interface Decoration {
	id: number;
	name: string;
	category: 'still' | 'animated';
}

export class Create extends Component {
	static auth = true;

	decorations: { still: Decoration[]; animated: Decoration[] } = { still: [], animated: [] };
	preview!: HTMLElement;
	noPreview!: HTMLElement;
	videoPreview!: HTMLVideoElement;
	imagePreview!: HTMLImageElement;
	layers!: HTMLElement[];
	actions!: HTMLElement;
	sourceSelection!: HTMLElement;
	selectCamera!: HTMLButtonElement;
	selectUpload!: HTMLButtonElement;
	uploadInput!: HTMLInputElement;
	capture!: HTMLButtonElement;
	cancelCapture!: HTMLButtonElement;
	submit!: HTMLButtonElement;
	decorationSelector!: HTMLElement;
	currentDecorations: Decoration[] = [];

	create(): void {
		this.layers = [];
		this.preview = DOM.create('div', {
			className: 'flex items-center justify-center', // sticky top-4 z-30 && bg-gray-200 dark:bg-gray-600
		});
		this.videoPreview = DOM.create('video', { className: 'preview', loop: true, autoplay: true, volume: 0 });
		this.imagePreview = DOM.create('img', { className: 'preview' });
		this.selectCamera = DOM.button('primary', 'camera', 'Camera');
		this.selectCamera.classList.add('rounded-tr-none', 'rounded-br-none');
		this.selectUpload = DOM.button('secondary', 'upload', 'Upload');
		this.selectUpload.classList.add('border-l-0', 'rounded-tl-none', 'rounded-bl-none');
		this.uploadInput = DOM.create('input', {
			className: 'hidden',
			id: 'create-upload',
			name: 'create-upload',
			type: 'file',
			accept: '.png,.jpg,.jpeg,.gif,.mp4',
		});
		this.uploadInput.classList.add('border-l-0', 'rounded-tl-none', 'rounded-bl-none');
		this.sourceSelection = DOM.create('div', {
			className: 'flex',
			childs: [this.selectCamera, this.selectUpload, this.uploadInput],
		});
		this.capture = DOM.button('primary', 'camera', 'Capture');
		this.capture.classList.add('mr-2', 'hidden');
		this.cancelCapture = DOM.button('error', 'x-circle', 'Cancel');
		this.cancelCapture.classList.add('hidden');
		this.submit = DOM.button('success', 'plus-circle', 'Submit');
		this.actions = DOM.create('div', {
			className: 'flex flex-row flex-nowrap justify-between mt-2',
			childs: [
				this.sourceSelection,
				DOM.create('div', { childs: [this.capture, this.cancelCapture] }),
				this.submit,
			],
		});
		this.decorationSelector = DOM.create('div', { className: 'decorations' });
	}

	async data(): Promise<void> {
		const response = await Http.get<{ list: Decoration[] }>('/api/decorations');
		if (response.ok) {
			this.decorations = { still: [], animated: [] };
			for (const decoration of response.body.list) {
				if (decoration.category == 'still') {
					this.decorations.still.push(decoration);
				} else {
					this.decorations.animated.push(decoration);
				}
			}
		} else {
			Notification.show('danger', `Failed to load Decorations: ${response.body.error}`);
		}
	}

	private enableCapture() {
		this.capture.classList.remove('hidden');
		this.cancelCapture.classList.add('hidden');
	}

	private enableCamera(): void {
		DOM.clear(this.preview);
		this.capture.classList.add('hidden');
		this.cancelCapture.classList.add('hidden');
		this.preview.appendChild(
			Alert.make('info', 'Allow the Camera permission on the top left to be able to use it.')
		);
		this.submit.disabled = true;
		navigator.mediaDevices
			.getUserMedia({ audio: false, video: { width: { min: 1280 }, height: { min: 720 }, frameRate: 30 } })
			.then((stream) => {
				DOM.clear(this.preview);
				this.enableCapture();
				this.preview.appendChild(this.videoPreview);
				this.videoPreview.srcObject = stream;
				this.videoPreview.src = '';
				this.capture.classList.remove('hidden');
				this.cancelCapture.classList.add('hidden');
				this.submit.disabled = false;
			})
			.catch((error) => {
				DOM.clear(this.preview);
				this.capture.classList.add('hidden');
				this.cancelCapture.classList.add('hidden');
				this.preview.appendChild(
					Alert.make('danger', 'Could not access your Camera, please allow this page to use your Camera.')
				);
			});
	}

	private setCapturePreview(image: string): void {
		this.imagePreview.src = image;
		this.videoPreview.remove();
		this.preview.appendChild(this.imagePreview);
		this.capture.classList.add('hidden');
		this.cancelCapture.classList.remove('hidden');
		this.submit.disabled = false;
	}

	private setUploadPreview(file: string): void {
		DOM.clear(this.preview);
		const isVideo = /^data:video/.test(file);
		if (isVideo) {
			this.videoPreview.srcObject = null;
			this.videoPreview.src = file;
			this.preview.appendChild(this.videoPreview);
		} else {
			this.imagePreview.src = file;
			this.preview.appendChild(this.imagePreview);
		}
		this.capture.classList.add('hidden');
		this.cancelCapture.classList.add('hidden');
		this.submit.disabled = false;
	}

	bind(): void {
		this.videoPreview.addEventListener('loadedmetadata', (event) => {
			this.videoPreview.play();
		});
		this.selectCamera.addEventListener('click', (event) => {
			event.preventDefault();
			this.enableCamera();
		});
		this.selectUpload.addEventListener('click', (event) => {
			event.preventDefault();
			this.uploadInput.click();
		});
		this.uploadInput.addEventListener('change', (event) => {
			console.log(event);
			if (this.uploadInput.value && this.uploadInput.files?.length) {
				const reader = new FileReader();
				reader.readAsDataURL(this.uploadInput.files[0]);
				reader.addEventListener('load', () => {
					this.setUploadPreview(reader.result as string);
				});
			}
		});
		this.capture.addEventListener('click', (event) => {
			event.preventDefault();
			const canvas = DOM.create('canvas');
			canvas.width = this.videoPreview.videoWidth;
			canvas.height = this.videoPreview.videoHeight;
			const context = canvas.getContext('2d')!;
			context.drawImage(this.videoPreview, 0, 0, canvas.width, canvas.height);
			const image = canvas.toDataURL('image/png');
			this.setCapturePreview(image);
		});
		this.cancelCapture.addEventListener('click', (event) => {
			event.preventDefault();
			this.imagePreview.src = '';
			this.imagePreview.remove();
			this.preview.appendChild(this.videoPreview);
			this.videoPreview.play();
			this.capture.classList.remove('hidden');
			this.cancelCapture.classList.add('hidden');
			this.submit.disabled = true;
		});
	}

	private createDecoration(decoration: Decoration): void {
		const visual = DOM.create(decoration.category == 'still' ? 'img' : 'video', {
			src: `/decorations/${decoration.name}`,
			autoplay: true,
			loop: true,
			volume: 0,
		});
		const card = DOM.create('div', {
			className: 'card',
			childs: [visual],
		});
		card.addEventListener('click', (event) => {
			event.preventDefault();
			this.currentDecorations.push(decoration);
		});
		this.decorationSelector.appendChild(card);
	}

	render(): void {
		for (const decoration of this.decorations.still) {
			this.createDecoration(decoration);
		}
		for (const decoration of this.decorations.animated) {
			this.createDecoration(decoration);
		}
		this.cancelCapture.classList.add('hidden');
		this.submit.disabled = true;
		DOM.append(this.parent, this.preview, this.actions, this.decorationSelector);
		this.enableCamera();
	}
}
