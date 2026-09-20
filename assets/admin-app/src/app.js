import { useEffect, useState } from 'react';
import { fetchModules, updateModule } from './api';
import Sidebar from './components/sidebar';
import Dashboard from './components/dashboard';
import ModulePage from './components/module-page';

export default function App() {
	const [ modules, setModules ] = useState( null );
	const [ error, setError ] = useState( null );
	const [ active, setActive ] = useState( 'dashboard' );
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

	const activeModule = modules?.find( ( module ) => module.slug === active );

	return (
		<div className="upcore-app">
			<header className="upcore-header">
				<img src={ window.upcoreAdmin?.logoUrl } alt="UpCore" />
			</header>

			{ error && <p className="upcore-error">{ error }</p> }

			{ ! modules && ! error && <p>Carregando…</p> }

			{ modules && (
				<div className="upcore-layout">
					<Sidebar
						modules={ modules }
						active={ active }
						onSelect={ setActive }
					/>

					<main className="upcore-main">
						{ active === 'dashboard' && (
							<Dashboard
								modules={ modules }
								onSelect={ setActive }
							/>
						) }

						{ activeModule && (
							<ModulePage
								module={ activeModule }
								onToggle={ handleToggle }
								pending={ pendingSlug }
								onSaveConfig={ handleSaveConfig }
								savingConfig={ savingConfigSlug === activeModule.slug }
							/>
						) }
					</main>
				</div>
			) }
		</div>
	);
}
