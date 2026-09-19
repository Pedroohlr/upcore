const TABS = [
	{ key: 'dashboard', label: 'Dashboard' },
	{ key: 'security', label: 'Seguranca' },
	{ key: 'performance', label: 'Performance' },
];

export default function Nav( { active, onChange } ) {
	return (
		<nav className="upcore-nav">
			{ TABS.map( ( tab ) => (
				<button
					key={ tab.key }
					type="button"
					className={
						'upcore-nav__item' +
						( tab.key === active ? ' is-active' : '' )
					}
					onClick={ () => onChange( tab.key ) }
				>
					{ tab.label }
				</button>
			) ) }
		</nav>
	);
}
