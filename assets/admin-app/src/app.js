import { useEffect, useState } from 'react';
import { fetchModules, updateModule } from './api';
import Nav from './components/nav';
import Dashboard from './components/dashboard';
import ModuleList from './components/module-list';

export default function App() {
	const [ modules, setModules ] = useState( null );
	const [ error, setError ] = useState( null );
	const [ tab, setTab ] = useState( 'dashboard' );
	const [ pendingSlug, setPendingSlug ] = useState( null );
	const [ savingConfigSlug, setSavingConfigSlug ] = useState( null );

	useEffect( () => {
		fetchModules()
			.then( setModules )
			.catch( () => setError( 'Nao foi possivel carregar os modulos.' ) );
	}, [] );

	function applyUpdate( updated ) {
		setModules( ( current ) =>
			current.map( ( module ) =>
				module.slug === updated.slug ? updated : module
			)
		);
	}

	async function handleToggle( slug, enabled ) {
		setPendingSlug( slug );
		setError( null );

		try {
			applyUpdate( await updateModule( slug, { enabled } ) );
		} catch ( err ) {
			setError( err?.message ?? 'Nao foi possivel atualizar o modulo.' );
		} finally {
			setPendingSlug( null );
		}
	}

	async function handleSaveConfig( slug, config ) {
		setSavingConfigSlug( slug );
		setError( null );

		try {
			applyUpdate( await updateModule( slug, { config } ) );
		} catch ( err ) {
			setError( err?.message ?? 'Nao foi possivel salvar a configuracao.' );
		} finally {
			setSavingConfigSlug( null );
		}
	}

	return (
		<div>
			<header className="upcore-header">
				<img src={ window.upcoreAdmin?.logoUrl } alt="UpCore" />
			</header>

			<Nav active={ tab } onChange={ setTab } />

			{ error && <p className="upcore-error">{ error }</p> }

			{ ! modules && ! error && <p>Carregando…</p> }

			{ modules && tab === 'dashboard' && (
				<Dashboard modules={ modules } />
			) }

			{ modules && tab !== 'dashboard' && (
				<ModuleList
					modules={ modules.filter(
						( module ) => module.category === tab
					) }
					onToggle={ handleToggle }
					pendingSlug={ pendingSlug }
					onSaveConfig={ handleSaveConfig }
					savingConfigSlug={ savingConfigSlug }
				/>
			) }
		</div>
	);
}
