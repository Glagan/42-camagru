export interface HttpResponse<T extends object> {
	body: T;
	ok: boolean;
	status: number;
	headers: HeadersInit;
}

export interface ValidHttpResponse<T extends object> extends HttpResponse<T> {
	ok: true;
}

export interface InvalidHttpResponse<E extends object> extends HttpResponse<E> {
	ok: false;
}

export type HttpResponseWithError<T extends object, E extends { error: string }> =
	| ValidHttpResponse<T>
	| InvalidHttpResponse<E>;

type JSONBody = BodyInit | Object | null;

type RequestInitJSON = {
	method: string;
	headers?: HeadersInit;
	credentials?: RequestCredentials;
	mode?: RequestMode;
	body?: JSONBody;
};

/**
 * Wrapper around fetch to handle errors.
 */
export class Http {
	private static send<T extends object, E extends { error: string } = { error: string }>(
		url: string,
		options: RequestInitJSON
	): Promise<HttpResponseWithError<T, E>> {
		if (options.body) {
			if (options.headers) {
				if (options.headers instanceof Headers) {
					options.headers.append('Content-Type', 'application/json');
				} else if (Array.isArray(options.headers)) {
					options.headers.push(['Content-Type', 'application/json']);
				} else {
					options.headers['Content-Type'] = 'application/json';
				}
			} else {
				options.headers = { 'Content-Type': 'application/json' };
			}
			options.body = JSON.stringify(options.body);
		}
		return fetch(url, { credentials: 'same-origin', ...(options as RequestInit) })
			.then(async (response) => {
				const body = await response.json();
				return {
					body,
					ok: response.ok,
					status: response.status,
					headers: response.headers,
				};
			})
			.catch(async (error) => {
				if (error instanceof Response) {
					const body = await error.json();
					return {
						body,
						ok: false,
						status: error.status,
						headers: error.headers,
					};
				}
				return {
					body: { error: 'Failed to send a Request.' } as E,
					ok: false,
					status: 0,
					headers: {},
				};
			});
	}

	/**
	 * Send a GET request with an expected JSON Response.
	 */
	static get<T extends object, E extends { error: string } = { error: string }>(
		url: string
	): Promise<HttpResponseWithError<T, E>> {
		return Http.send<T, E>(url, { method: 'GET' });
	}

	/**
	 * Send a POST request with an expected JSON Response.
	 */
	static post<T extends object, E extends { error: string } = { error: string }>(
		url: string,
		body?: JSONBody
	): Promise<HttpResponseWithError<T, E>> {
		return Http.send<T, E>(url, { method: 'POST', body });
	}

	/**
	 * Send a PUT request with an expected JSON Response.
	 */
	static put<T extends object, E extends { error: string } = { error: string }>(
		url: string,
		body?: JSONBody
	): Promise<HttpResponseWithError<T, E>> {
		return Http.send<T, E>(url, { method: 'PUT', body });
	}

	/**
	 * Send a PATCH request with an expected JSON Response.
	 */
	static patch<T extends object, E extends { error: string } = { error: string }>(
		url: string,
		body?: JSONBody
	): Promise<HttpResponseWithError<T, E>> {
		return Http.send<T, E>(url, { method: 'PATCH', body });
	}

	/**
	 * Send a DELETE request with an expected JSON Response.
	 */
	static delete<T extends object, E extends { error: string } = { error: string }>(
		url: string,
		body?: JSONBody
	): Promise<HttpResponseWithError<T, E>> {
		return Http.send<T, E>(url, { method: 'DELETE', body });
	}
}
