export default function Toggle( { checked, disabled, onChange } ) {
	return (
		<label className="upcore-toggle">
			<input
				type="checkbox"
				checked={ checked }
				disabled={ disabled }
				onChange={ ( event ) => onChange( event.target.checked ) }
			/>
			<span className="upcore-toggle__track" />
			<span className="upcore-toggle__thumb" />
		</label>
	);
}
