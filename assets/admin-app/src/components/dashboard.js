function countByCategory( modules, category ) {
	const inCategory = modules.filter( ( module ) => module.category === category );
	const active = inCategory.filter( ( module ) => module.enabled );

	return { active: active.length, total: inCategory.length };
}

function collectHighlights( modules ) {
	const highlights = [];

	modules.forEach( ( module ) => {
		( module.stats ?? [] ).forEach( ( stat ) => {
			if ( stat.count > 0 ) {
				highlights.push( {
					key: `${ module.slug }:${ stat.key }`,
					label: stat.label,
					count: stat.count,
					moduleLabel: module.label,
				} );
			}
		} );
	} );

	return highlights.sort( ( a, b ) => b.count - a.count ).slice( 0, 6 );
}

export default function Dashboard( { modules } ) {
	const security = countByCategory( modules, 'security' );
	const performance = countByCategory( modules, 'performance' );
	const highlights = collectHighlights( modules );

	return (
		<div>
			<div className="upcore-cards">
				<div className="upcore-card">
					<span className="upcore-card__value">
						{ security.active }/{ security.total }
					</span>
					<span className="upcore-card__label">
						Modulos de seguranca ativos
					</span>
				</div>
				<div className="upcore-card">
					<span className="upcore-card__value">
						{ performance.active }/{ performance.total }
					</span>
					<span className="upcore-card__label">
						Modulos de performance ativos
					</span>
				</div>
			</div>

			<section className="upcore-section">
				<h2>Atividade recente</h2>
				{ highlights.length === 0 && (
					<div className="upcore-module-list upcore-empty-state">
						Nenhum evento registrado ainda. Os numeros aparecem aqui
						conforme os modulos ativos forem bloqueando tentativas.
					</div>
				) }
				{ highlights.length > 0 && (
					<div className="upcore-module-list">
						{ highlights.map( ( item ) => (
							<div className="upcore-stat-row" key={ item.key }>
								<span className="upcore-stat-row__label">
									{ item.label }
									<span className="upcore-stat-row__module">
										{ item.moduleLabel }
									</span>
								</span>
								<span className="upcore-stat-row__count">
									{ item.count }
								</span>
							</div>
						) ) }
					</div>
				) }
			</section>
		</div>
	);
}
