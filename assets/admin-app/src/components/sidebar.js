const CATEGORY_LABELS = {
	security: 'Seguranca',
	performance: 'Performance',
};

function SidebarItem( { module, active, onSelect } ) {
	return (
		<button
			type="button"
			className={
				'upcore-sidebar__item' + ( active ? ' is-active' : '' )
			}
			onClick={ () => onSelect( module.slug ) }
		>
			<span
				className={
					'upcore-sidebar__dot' +
					( module.enabled ? ' is-on' : '' )
				}
			/>
			{ module.label }
		</button>
	);
}

export default function Sidebar( { modules, active, onSelect } ) {
	const categories = [ 'security', 'performance' ];

	return (
		<nav className="upcore-sidebar">
			<button
				type="button"
				className={
					'upcore-sidebar__item upcore-sidebar__item--top' +
					( active === 'dashboard' ? ' is-active' : '' )
				}
				onClick={ () => onSelect( 'dashboard' ) }
			>
				Dashboard
			</button>

			{ categories.map( ( category ) => (
				<div className="upcore-sidebar__group" key={ category }>
					<span className="upcore-sidebar__group-label">
						{ CATEGORY_LABELS[ category ] }
					</span>
					{ modules
						.filter( ( module ) => module.category === category )
						.map( ( module ) => (
							<SidebarItem
								key={ module.slug }
								module={ module }
								active={ active === module.slug }
								onSelect={ onSelect }
							/>
						) ) }
				</div>
			) ) }
		</nav>
	);
}
