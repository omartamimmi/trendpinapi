import './bootstrap';
import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';

createInertiaApp({
    resolve: name => {
        // Support both .jsx and .tsx files
        const pages = import.meta.glob('./Pages/**/*.{jsx,tsx}', { eager: true });

        // Try .tsx first, then .jsx
        let page = pages[`./Pages/${name}.tsx`] || pages[`./Pages/${name}.jsx`];

        if (!page) {
            throw new Error(`Page not found: ${name}`);
        }
        return page;
    },
    setup({ el, App, props }) {
        createRoot(el).render(<App {...props} />);
    },
    progress: {
        color: '#E91E8C',
        showSpinner: true,
    },
});
