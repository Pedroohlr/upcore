import { useEffect, useState } from 'react';
import { fetchModules, updateModule } from './api';
import ModuleList from './components/module-list';

const CATEGORY_LABELS = {
	security: 'Seguranca',
	performance: 'Performance',
};

const CATEGORY_ORDER = [ 'security', 'performance' ];

export default function App() {
	const [ modules, setModules ] = useState( null );
	const [ error, setError ] = useState( null );
	const [ pendingSlug, setPendingSlug ] = useState( null );

	useEffect( () => {
		fetchModules()
			.then( setModules )
			.catch( () => setError( 'Nao foi possivel carregar os modulos.' ) );
	}, [] );

	async function handleToggle( slug, enabled ) {
		setPendingSlug( slug );
		setError( null );

		try {
			const updated = await updateModule( slug, enabled );
			setModules( ( current ) =>
				current.map( ( module ) =>
					module.slug === slug ? updated : module
				)
			);
		} catch ( err ) {
			setError( err?.message ?? 'Nao foi possivel atualizar o modulo.' );
		} finally {
			setPendingSlug( null );
		}
	}

	return (
		<div>
			<header className="upcore-header">
				<img src={ window.upcoreAdmin?.logoUrl } alt="UpCore" />
			</header>

			{ error && <p className="upcore-error">{ error }</p> }

			{ ! modules && ! error && <p>Carregando…</p> }

			{ modules &&
				CATEGORY_ORDER.map( ( category ) => (
					<section className="upcore-section" key={ category }>
						<h2>{ CATEGORY_LABELS[ category ] }</h2>
						<ModuleList
							modules={ modules.filter(
								( module ) => module.category === category
							) }
							onToggle={ handleToggle }
							pendingSlug={ pendingSlug }
						/>
					</section>
				) ) }
		</div>
	);
}
