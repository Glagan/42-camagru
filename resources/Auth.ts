import { Http } from './Utility/Http';

type StatusResponse = { user: User } | { error: string };

export class Auth {
	loggedIn!: boolean;
	user!: User;
	pendingStatus: Promise<{ loggedIn: boolean; user?: User }> | undefined;

	private default() {
		this.loggedIn = false;
		this.user = {
			id: 0,
			username: '',
			email: '',
			verified: false,
			receiveComments: true,
		};
	}

	constructor() {
		this.default();
	}

	login(user: User) {
		this.loggedIn = true;
		this.user = user;
	}

	logout() {
		this.default();
	}

	get isLoggedIn(): Promise<boolean> {
		if (this.pendingStatus)
			return this.pendingStatus.then((status) => {
				return status.loggedIn;
			});
		return new Promise((resolve) => resolve(this.loggedIn));
	}

	/**
	 * Create an object with a resolve function and a Promise.
	 * Calling the returned resolve function will resolve the connected Promise.
	 */
	private promise() {
		let _resolve: any = undefined;
		const _promise: any = new Promise((r) => (_resolve = r));
		function resolve(...args: any) {
			_resolve(...args);
		}
		function promise() {
			return _promise;
		}
		return { resolve, promise };
	}

	async status(): Promise<boolean> {
		if (!this.pendingStatus) {
			// Create a "manual" promise that will be resolved later
			const state = this.promise();
			this.pendingStatus = state.promise();
			const response = await Http.get<StatusResponse>('/api/status');
			this.default();
			if (response.ok && !('error' in response.body)) {
				this.loggedIn = true;
				this.user = response.body.user;
			}
			// Resolve this.pendingStatus
			delete this.pendingStatus;
			state.resolve({ loggedIn: this.loggedIn });
			return this.loggedIn;
		}
		return this.pendingStatus.then((status) => {
			return status.loggedIn;
		});
	}
}
