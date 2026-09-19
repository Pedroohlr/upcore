import { useState } from 'react';
import { asObject } from '../utils';

export default function ModuleFields( { fields, config, onSave, saving } ) {
	const [ values, setValues ] = useState( () => ( { ...asObject( config ) } ) );
	const [ dirty, setDirty ] = useState( false );

	function handleChange( key, value ) {
		setValues( ( current ) => ( { ...current, [ key ]: value } ) );
		setDirty( true );
	}

	function handleSave() {
		onSave( values );
		setDirty( false );
	}

	return (
		<div className="upcore-fields">
			{ fields.map( ( field ) => (
				<label className="upcore-field" key={ field.key }>
					<span className="upcore-field__label">{ field.label }</span>
					<input
						type={ field.type === 'password' ? 'password' : 'text' }
						value={ values[ field.key ] ?? '' }
						onChange={ ( event ) =>
							handleChange( field.key, event.target.value )
						}
					/>
					{ field.description && (
						<span className="upcore-field__description">
							{ field.description }
						</span>
					) }
				</label>
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
