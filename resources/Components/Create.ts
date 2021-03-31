import { Component } from '../Component';
import { Alert } from '../UI/Alert';
import { DOM } from '../Utility/DOM';

export class Create extends Component {
	static auth = true;

	preview!: HTMLElement;
	noPreview!: HTMLElement;
	videoPreview!: HTMLVideoElement;
	imagePreview!: HTMLImageElement;
	layers!: HTMLElement[];
	actions!: HTMLElement;
	sourceSelection!: HTMLElement;
	selectCamera!: HTMLButtonElement;
	selectUpload!: HTMLButtonElement;
	capture!: HTMLButtonElement;
	cancelCapture!: HTMLButtonElement;
	submit!: HTMLButtonElement;
	decorations!: HTMLElement;

	create(): void {
		this.layers = [];
		this.preview = DOM.create('div', {
			className: 'sticky top-4 z-30 bg-gray-100 dark:bg-gray-600 flex items-center justify-center',
		});
		this.videoPreview = DOM.create('video', { className: 'shadow-md w-full', loop: true, volume: 0 });
		this.imagePreview = DOM.create('img', { className: 'shadow-md w-full' });
		this.selectCamera = DOM.button('primary', 'camera', 'Camera');
		this.selectCamera.classList.add('rounded-tr-none', 'rounded-br-none');
		this.selectUpload = DOM.button('secondary', 'upload', 'Upload');
		this.selectUpload.classList.add('border-l-0', 'rounded-tl-none', 'rounded-bl-none');
		this.sourceSelection = DOM.create('div', { childs: [this.selectCamera, this.selectUpload] });
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
		this.decorations = DOM.create('div', {
			className:
				'mt-2 grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-2 auto-rows-min justify-items-center p-4',
		});
	}

	private enableCapture() {
		this.capture.classList.remove('hidden');
		this.cancelCapture.classList.add('hidden');
	}

	private enableCamera(): void {
		DOM.clear(this.preview);
		this.cancelCapture.classList.add('hidden');
		this.preview.appendChild(
			Alert.make('info', 'Allow the Camera permission on the top left to be able to use it.')
		);
		navigator.mediaDevices
			.getUserMedia({ audio: false, video: true })
			.then((stream) => {
				DOM.clear(this.preview);
				this.enableCapture();
				this.preview.appendChild(this.videoPreview);
				this.videoPreview.srcObject = stream;
			})
			.catch((error) => {
				DOM.clear(this.preview);
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
		});
		this.capture.addEventListener('click', (event) => {
			event.preventDefault();
			const canvas = DOM.create('canvas');
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

	render(): void {
		this.cancelCapture.classList.add('hidden');
		this.submit.disabled = true;
		DOM.append(this.parent, this.preview, this.actions, this.decorations);
		this.enableCamera();
	}
}
