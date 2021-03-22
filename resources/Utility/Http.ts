export interface HttpResponse<T = object> {
	body: T;
	ok: boolean;
	status: number;
	headers: HeadersInit;
}

/**
 * Wrapper around fetch to handle errors.
 */
export class Http {
	private static send<T extends object>(url: string, options: RequestInit): Promise<HttpResponse<T>> {
		return fetch(url, { credentials: 'same-origin', ...options })
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
					body: {} as T,
					ok: false,
					status: 0,
					headers: {},
				};
			});
	}

	/**
	 * Send a GET request with an expected JSON Response.
	 * @param url
	 */
	static get<T extends object>(url: string): Promise<HttpResponse<T>> {
		return Http.send(url, { method: 'GET' });
	}

	/**
	 * Send a POST request with an expected JSON Response.
	 * @param url
	 * @param body
	 */
	static post<T extends object>(url: string, body: BodyInit): Promise<HttpResponse<T>> {
		return Http.send(url, { method: 'POST', body });
	}

	/**
	 * Send a PUT request with an expected JSON Response.
	 * @param url
	 * @param body
	 */
	static put<T extends object>(url: string, body: BodyInit): Promise<HttpResponse<T>> {
		return Http.send(url, { method: 'POST', body });
	}

	/**
	 * Send a PATCH request with an expected JSON Response.
	 * @param url
	 * @param body
	 */
	static patch<T extends object>(url: string, body: BodyInit): Promise<HttpResponse<T>> {
		return Http.send(url, { method: 'POST', body });
	}

	/**
	 * Send a DELETE request with an expected JSON Response.
	 * @param url
	 * @param body
	 */
	static delete<T extends object>(url: string, body: BodyInit): Promise<HttpResponse<T>> {
		return Http.send(url, { method: 'POST', body });
	}
}
