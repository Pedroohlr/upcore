import ModuleFields from './module-fields';

export default function ModuleList( {
	modules,
	onToggle,
	pendingSlug,
	onSaveConfig,
	savingConfigSlug,
} ) {
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
					<div className="upcore-module__row">
						<div className="upcore-module__info">
							<span className="upcore-module__label">
								{ module.label }
								{ module.status !== 'ready' && (
									<span className="upcore-badge">
										Em breve
									</span>
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
									onToggle(
										module.slug,
										event.target.checked
									)
								}
							/>
							<span className="upcore-toggle__track" />
							<span className="upcore-toggle__thumb" />
						</label>
					</div>

					{ module.fields?.length > 0 && (
						<ModuleFields
							fields={ module.fields }
							config={ module.config }
							saving={ savingConfigSlug === module.slug }
							onSave={ ( values ) =>
								onSaveConfig( module.slug, values )
							}
						/>
					) }

					{ module.stats?.some( ( stat ) => stat.count > 0 ) && (
						<div className="upcore-module__stats">
							{ module.stats
								.filter( ( stat ) => stat.count > 0 )
								.map( ( stat ) => (
									<span
										className="upcore-stat-pill"
										key={ stat.key }
									>
										{ stat.label }: { stat.count }
									</span>
								) ) }
						</div>
					) }
				</div>
			) ) }
		</div>
	);
}
