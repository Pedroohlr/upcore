import Toggle from './toggle';
import ModuleFields from './module-fields';

export default function ModulePage( {
	module,
	onToggle,
	pending,
	onSaveConfig,
	savingConfig,
} ) {
	return (
		<div className="upcore-page">
			<div className="upcore-page__header">
				<div>
					<h1 className="upcore-page__title">
						{ module.label }
						{ module.status !== 'ready' && (
							<span className="upcore-badge">Em breve</span>
						) }
					</h1>
					<p className="upcore-page__description">
						{ module.description }
					</p>
				</div>

				<Toggle
					checked={ module.enabled }
					disabled={
						module.status !== 'ready' || pending === module.slug
					}
					onChange={ ( checked ) =>
						onToggle( module.slug, checked )
					}
				/>
			</div>

			{ module.stats?.length > 0 && (
				<div className="upcore-page__stats">
					{ module.stats.map( ( stat ) => (
						<div className="upcore-stat-card" key={ stat.key }>
							<span className="upcore-stat-card__value">
								{ stat.count }
							</span>
							<span className="upcore-stat-card__label">
								{ stat.label }
							</span>
						</div>
					) ) }
				</div>
			) }

			{ module.fields?.length > 0 && (
				<div className="upcore-page__section">
					<h2>Configuracao</h2>
					<ModuleFields
						key={ module.slug }
						fields={ module.fields }
						config={ module.config }
						saving={ savingConfig }
						onSave={ ( values ) =>
							onSaveConfig( module.slug, values )
						}
					/>
				</div>
			) }
		</div>
	);
}
