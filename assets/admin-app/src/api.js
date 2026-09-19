import apiFetch from '@wordpress/api-fetch';

const REST_URL = window.upcoreAdmin?.restUrl ?? '';

apiFetch.use(apiFetch.createNonceMiddleware(window.upcoreAdmin?.nonce ?? ''));

export function fetchModules() {
	return apiFetch({ url: `${ REST_URL }/modules` });
}

export function updateModule( slug, enabled ) {
	return apiFetch( {
		url: `${ REST_URL }/modules/${ slug }`,
		method: 'POST',
		data: { enabled },
	} );
}
