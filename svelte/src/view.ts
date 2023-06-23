import Navbar from './components/Navbar.svelte';
import View from './components/View.svelte';
import { createStore, type ViewParams } from './lib/store';

import './global.css';

async function init() {
    const viewTarget = document.getElementById('local-mail-view');
    const navbarTarget = document.getElementById('local-mail-navbar');

    // Get initial data from script tag.
    const data = (window as any).local_mail_view_data || {};

    const store = await createStore(data);
    if (viewTarget) {
        new View({ target: viewTarget, props: { store } });
    }
    if (navbarTarget) {
        // Remove fallback link created in local_mail_render_navbar_output.
        navbarTarget.innerHTML = '';

        // Instantiate Navbar component with current store data.
        const navbar = new Navbar({
            target: navbarTarget,
            props: {
                settings: store.get().settings,
                strings: store.get().strings,
                unread: store.get().unread,
                drafts: store.get().drafts,
                courses: store.get().courses,
                labels: store.get().labels,
                onClick: (params: ViewParams) => store.navigate(params),
            },
        });

        // Update properties when store data changes.
        store.subscribe((state) => {
            navbar.$set({
                settings: state.settings,
                strings: state.strings,
                unread: state.unread,
                drafts: state.drafts,
                courses: state.courses,
                labels: state.labels,
                params: state.params,
                onClick: (params: ViewParams) => store.navigate(params),
            });
        });
    }
}

init();
