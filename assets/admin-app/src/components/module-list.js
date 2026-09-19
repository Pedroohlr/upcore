export default function ModuleList( { modules, onToggle, pendingSlug } ) {
	if ( ! modules.length ) {
		return (
			<div className="upcore-module-list upcore-empty-state">
				Nenhum modulo nesta categoria.
			</div>
		);
	}

	return (
		<div className="upcore-module-list">
			{ modules.map( ( module ) => (
				<div className="upcore-module" key={ module.slug }>
					<div className="upcore-module__info">
						<span className="upcore-module__label">
							{ module.label }
							{ module.status !== 'ready' && (
								<span className="upcore-badge">Em breve</span>
							) }
						</span>
						<span className="upcore-module__description">
							{ module.description }
						</span>
					</div>

					<label className="upcore-toggle">
						<input
							type="checkbox"
							checked={ module.enabled }
							disabled={
								module.status !== 'ready' ||
								pendingSlug === module.slug
							}
							onChange={ ( event ) =>
								onToggle( module.slug, event.target.checked )
							}
						/>
						<span className="upcore-toggle__track" />
						<span className="upcore-toggle__thumb" />
					</label>
				</div>
			) ) }
		</div>
	);
}
