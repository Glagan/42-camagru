import { Component } from '../Component';
import { Alert } from '../UI/Alert';
import { Notification } from '../UI/Notification';
import { DOM } from '../Utility/DOM';
import { Http } from '../Utility/Http';

export type DecorationPosition = 'top-left' | 'top-right' | 'bottom-right' | 'bottom-left';
export type XYPosition = { x: number; y: number };

export interface Decoration {
	id: number;
	name: string;
	category: 'still' | 'animated';
	position: DecorationPosition;
}

const WIDTH = 1280;
const HEIGHT = 720;
const LOADING_IMG =
	'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKAQMAAAC3/F3+AAAAA1BMVEUzzMx0ynDKAAAACklEQVR4XmPACwAAHgAB5s72BgAAAABJRU5ErkJggg==';
const LOADING_VIDEO = `data:image/webm;base64,GkXfo59ChoEBQveBAULygQRC84EIQoKEd2VibUKHgQJChYECGFOAZwEAAAAAAAJ+EU2bdLtNu4tTq4QVSalmU6yB5U27jFOrhBZUrmtTrIIBHE27jFOrhBJUw2dTrIIBXE27jFOrhBxTu2tTrIICaOwBAAAAAAAAnAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABVJqWayKtexgw9CQE2AjUxhdmY1OC4yOS4xMDBXQY1MYXZmNTguMjkuMTAwRImIQEQAAAAAAAAWVK5ru64BAAAAAAAAMteBAXPFgQGcgQAitZyDdW5khoVWX1ZQOIOBASPjg4QCYloA4AEAAAAAAAAGsIEKuoEKElTDZ0C/c3MBAAAAAAAALmPAAQAAAAAAAABnyAEAAAAAAAAaRaOHRU5DT0RFUkSHjUxhdmY1OC4yOS4xMDBzcwEAAAAAAAA5Y8ABAAAAAAAABGPFgQFnyAEAAAAAAAAhRaOHRU5DT0RFUkSHlExhdmM1OC41NC4xMDAgbGlidnB4c3MBAAAAAAAAOmPAAQAAAAAAAARjxYEBZ8gBAAAAAAAAIkWjiERVUkFUSU9ORIeUMDA6MDA6MDAuMDQwMDAwMDAwAAAfQ7Z1wueBAKO9gQAAgLACAJ0BKgoACgAARwiFhYiFhIgCAgJ1qgP4Agz9KAD+90av/rgPzgPzgP5lv/8D9/gfv8D9/+BPABxTu2uRu4+zgQC3iveBAfGCAiHwgQM=`;

export class Create extends Component {
	static auth = true;

	previewRow!: HTMLElement;
	preview!: HTMLElement;
	videoPreview!: HTMLVideoElement;
	imagePreview!: HTMLImageElement;
	allowCamera!: HTMLElement;
	noCamera!: HTMLElement;
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
	visibleDecorations!: HTMLElement;

	decorations: { still: Decoration[]; animated: Decoration[] } = { still: [], animated: [] };
	dragState: { [key: string]: { node: HTMLElement; initial: XYPosition; active: boolean; current: XYPosition } } = {};
	currentDecorations: Decoration[] = [];

	create(): void {
		this.layers = [];
		this.videoPreview = DOM.create('video', { className: 'hidden preview', loop: true, autoplay: true, volume: 0 });
		this.imagePreview = DOM.create('img', { className: 'hidden preview' });
		this.allowCamera = Alert.make('info', 'Allow the Camera permission on the top left to be able to use it.');
		this.noCamera = Alert.make('danger', 'Error while accessing your Camera.');
		this.visibleDecorations = DOM.create('div', { className: 'preview-decorations' });
		this.preview = DOM.create('div', {
			className: 'relative',
			childs: [this.videoPreview, this.imagePreview, this.allowCamera, this.noCamera, this.visibleDecorations],
		});
		// sticky top-4 z-30 && bg-gray-200 dark:bg-gray-600
		this.previewRow = DOM.create('div', {
			className: 'flex items-center justify-center mt-2',
			childs: [this.preview],
		});
		this.selectCamera = DOM.button('primary', 'camera', 'Camera');
		this.selectCamera.classList.add('rounded-tr-none', 'rounded-br-none');
		this.selectUpload = DOM.button('secondary', 'upload', 'Upload');
		this.selectUpload.classList.add('border-l-0', 'rounded-tl-none', 'rounded-bl-none');
		this.uploadInput = DOM.create('input', {
			className: 'hidden',
			id: 'create-upload',
			name: 'create-upload',
			type: 'file',
			accept: '.png,.jpg,.jpeg,.gif,.mp4,.webm',
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
			childs: [DOM.create('div', { childs: [this.capture, this.cancelCapture] }), this.submit],
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

	private enableCapture(): void {
		this.capture.classList.remove('hidden');
		this.cancelCapture.classList.add('hidden');
	}

	private disableCapture(): void {
		this.capture.classList.add('hidden');
		this.cancelCapture.classList.add('hidden');
	}

	private hidePreviews(): void {
		this.imagePreview.classList.add('hidden');
		this.videoPreview.classList.add('hidden');
		this.allowCamera.classList.add('hidden');
		this.noCamera.classList.add('hidden');
	}

	private cameraMode(): void {
		this.hidePreviews();
		this.enableCapture();
		this.submit.disabled = true;
		this.allowCamera.classList.remove('hidden');
		this.noCamera.classList.add('hidden');
		navigator.mediaDevices
			.getUserMedia({
				audio: false,
				video: { width: { min: WIDTH, max: WIDTH }, height: { min: HEIGHT, max: HEIGHT }, frameRate: 30 },
			})
			.then((stream) => {
				this.videoPreview.classList.remove('hidden');
				this.videoPreview.src = '';
				this.videoPreview.srcObject = stream;
				this.enableCapture();
				this.submit.disabled = true;
				this.allowCamera.classList.add('hidden');
				this.noCamera.classList.add('hidden');
			})
			.catch((error) => {
				this.hidePreviews();
				this.disableCapture();
				this.allowCamera.classList.add('hidden');
				this.noCamera.classList.remove('hidden');
			});
	}

	private uploadMode(file: string): void {
		this.hidePreviews();
		this.disableCapture();
		this.submit.disabled = false;
		const isVideo = /^data:video/.test(file);
		if (isVideo) {
			this.videoPreview.srcObject = null;
			this.videoPreview.src = file;
			this.videoPreview.classList.remove('hidden');
		} else {
			this.imagePreview.src = file;
			this.imagePreview.classList.remove('hidden');
		}
	}

	private setCapturePreview(image: string): void {
		this.imagePreview.src = image;
		this.imagePreview.classList.remove('hidden');
		this.videoPreview.classList.add('hidden');
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
			this.cameraMode();
		});
		this.selectUpload.addEventListener('click', (event) => {
			event.preventDefault();
			this.uploadInput.click();
		});
		this.uploadInput.addEventListener('change', (event) => {
			if (this.uploadInput.value && this.uploadInput.files?.length) {
				const reader = new FileReader();
				reader.readAsDataURL(this.uploadInput.files[0]);
				reader.addEventListener('load', () => {
					this.uploadMode(reader.result as string);
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
			this.imagePreview.classList.add('hidden');
			this.videoPreview.classList.remove('hidden');
			this.videoPreview.play();
			this.capture.classList.remove('hidden');
			this.cancelCapture.classList.add('hidden');
			this.submit.disabled = true;
		});
		this.submit.addEventListener('click', async (event) => {
			event.preventDefault();
			const ratio = { x: WIDTH / this.preview.offsetWidth, y: HEIGHT / this.preview.offsetHeight };
			const response = await Http.post<{ success: string; id: number }>('/api/upload', {
				image: this.imagePreview.src,
				decorations: this.currentDecorations.map((d) => {
					const position = this.dragState[d.id].current;
					return { id: d.id, position: { x: position.x * ratio.x, y: position.y * ratio.y } };
				}),
			});
			if (response.ok) {
				Notification.show('success', `Nice.`);
			} else {
				Notification.show('danger', `Could not upload creation: ${response.body.error}`);
			}
		});
	}

	private createDecoration(decoration: Decoration, observer?: IntersectionObserver): HTMLElement {
		const isImage = decoration.category == 'still';
		let src = `/decorations/${decoration.name}`;
		let dataSrc = '';
		if (observer) {
			dataSrc = src;
			src = isImage ? LOADING_IMG : LOADING_VIDEO;
		}
		const node = DOM.create(isImage ? 'img' : 'video', {
			src: src,
			dataset: { src: dataSrc },
			autoplay: true,
			loop: true,
			volume: 0,
		});
		if (observer) observer.observe(node);
		return node;
	}

	private translate(node: HTMLElement, position: XYPosition): void {
		node.style.setProperty('--tw-translate-x', `${position.x}px`);
		node.style.setProperty('--tw-translate-y', `${position.y}px`);
	}

	private dragStart(event: TouchEvent | MouseEvent, id: number): void {
		event.preventDefault();
		const state = this.dragState[id];

		// Calculate
		const currentState: XYPosition = { x: 0, y: 0 };
		if (event.type === 'touchstart') {
			currentState.x = (event as TouchEvent).touches[0].clientX - state.current.x;
			currentState.y = (event as TouchEvent).touches[0].clientY - state.current.y;
		} else {
			currentState.x = (event as MouseEvent).clientX - state.current.x;
			currentState.y = (event as MouseEvent).clientY - state.current.y;
		}

		// Save
		this.dragState[id].initial = currentState;
		if (event.target === state.node) {
			this.dragState[id].active = true;
		}
	}

	private drag(event: TouchEvent | MouseEvent, id: number): void {
		const state = this.dragState[id];
		if (state.active) {
			event.preventDefault();
			if (event.type === 'touchmove') {
				state.current.x = (event as TouchEvent).touches[0].clientX - state.initial.x;
				state.current.y = (event as TouchEvent).touches[0].clientY - state.initial.y;
			} else {
				state.current.x = (event as MouseEvent).clientX - state.initial.x;
				state.current.y = (event as MouseEvent).clientY - state.initial.y;
			}
			this.translate(state.node, state.current);
		}
	}

	private dragEnd(_event: TouchEvent | MouseEvent, id: number): void {
		const state = this.dragState[id];
		state.initial.x = state.current.x;
		state.initial.y = state.current.y;
		state.active = false;
	}

	private displayDecoration(decoration: Decoration, observer: IntersectionObserver): void {
		const visual = this.createDecoration(decoration, observer);
		const card = DOM.create('div', {
			className: 'card',
			childs: [visual],
		});
		card.addEventListener('click', (event) => {
			event.preventDefault();
			//this.currentDecorations.push(decoration);
			this.currentDecorations = [decoration];
			const layer = this.createDecoration(decoration);

			// Default position
			this.translate(layer, { x: 0, y: 0 });

			// Calculate ratio and update decorations -- ratio = WIDTH / previewWidth;
			const ratio = { x: this.preview.offsetWidth / WIDTH, y: this.preview.offsetHeight / HEIGHT };
			layer.style.setProperty('--tw-scale-x', `${ratio.x}`);
			layer.style.setProperty('--tw-scale-y', `${ratio.y}`);

			// Adapted from https://www.kirupa.com/html5/drag.htm
			this.dragState[decoration.id] = {
				node: layer,
				initial: { x: 0, y: 0 },
				active: false,
				current: { x: 0, y: 0 },
			};
			layer.addEventListener('touchstart', (e) => this.dragStart(e, decoration.id), false);
			layer.addEventListener('touchend', (e) => this.dragEnd(e, decoration.id), false);
			layer.addEventListener('touchmove', (e) => this.drag(e, decoration.id), false);
			layer.addEventListener('mousedown', (e) => this.dragStart(e, decoration.id), false);
			layer.addEventListener('mouseup', (e) => this.dragEnd(e, decoration.id), false);
			layer.addEventListener('mousemove', (e) => this.drag(e, decoration.id), false);

			DOM.clear(this.visibleDecorations); // Remove with multiple decorations
			this.visibleDecorations.appendChild(layer);
		});
		this.decorationSelector.appendChild(card);
	}

	render(): void {
		// https://developer.mozilla.org/en-US/docs/Web/API/Intersection_Observer_API
		const observer = new IntersectionObserver((entries, observer) => {
			for (const entry of entries) {
				if (!entry.isIntersecting) return;
				const img = entry.target as HTMLImageElement;
				img.src = img.dataset.src!;
				observer.unobserve(entry.target);
			}
		});
		for (const decoration of this.decorations.still) {
			this.displayDecoration(decoration, observer);
		}
		for (const decoration of this.decorations.animated) {
			this.displayDecoration(decoration, observer);
		}
		this.cancelCapture.classList.add('hidden');
		this.submit.disabled = true;
		DOM.append(this.parent, this.sourceSelection, this.previewRow, this.actions, this.decorationSelector);
		this.cameraMode();
	}
}
