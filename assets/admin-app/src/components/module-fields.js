import { useState } from 'react';
import { asObject } from '../utils';

function FieldInput( { field, value, onChange } ) {
	if ( field.type === 'checkbox' ) {
		return (
			<label className="upcore-field upcore-field--checkbox">
				<input
					type="checkbox"
					checked={ !! value }
					onChange={ ( event ) =>
						onChange( event.target.checked )
					}
				/>
				<span className="upcore-field__label">{ field.label }</span>
				{ field.description && (
					<span className="upcore-field__description">
						{ field.description }
					</span>
				) }
			</label>
		);
	}

	const inputType =
		field.type === 'password' || field.type === 'number'
			? field.type
			: 'text';

	return (
		<label className="upcore-field">
			<span className="upcore-field__label">{ field.label }</span>
			<input
				type={ inputType }
				value={ value ?? '' }
				onChange={ ( event ) => onChange( event.target.value ) }
			/>
			{ field.description && (
				<span className="upcore-field__description">
					{ field.description }
				</span>
			) }
		</label>
	);
}

export default function ModuleFields( { fields, config, onSave, saving } ) {
	const [ values, setValues ] = useState( () => ( { ...asObject( config ) } ) );
	const [ dirty, setDirty ] = useState( false );

	function handleChange( key, value ) {
		setValues( ( current ) => ( { ...current, [ key ]: value } ) );
		setDirty( true );
	}

	async function handleSave() {
		const success = await onSave( values );

		if ( success !== false ) {
			setDirty( false );
		}
	}

	return (
		<div className="upcore-fields">
			{ fields.map( ( field ) => (
				<FieldInput
					key={ field.key }
					field={ field }
					value={ values[ field.key ] }
					onChange={ ( value ) => handleChange( field.key, value ) }
				/>
			) ) }
			<button
				type="button"
				className="upcore-button"
				disabled={ ! dirty || saving }
				onClick={ handleSave }
			>
				{ saving ? 'Salvando...' : 'Salvar configuracao' }
			</button>
		</div>
	);
}
