export function asObject( value ) {
	return value && ! Array.isArray( value ) && typeof value === 'object' ? value : {};
}
