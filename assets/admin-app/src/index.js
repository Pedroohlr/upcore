import { createRoot } from 'react-dom/client';
import App from './app';
import './style.scss';

const container = document.getElementById( 'upcore-admin-root' );

if ( container ) {
	createRoot( container ).render( <App /> );
}
