import './bootstrap';
import { createInertiaApp, router } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';
import "swiper/css";
import "swiper/css/navigation";
import "swiper/css/pagination";
import { ToastProvider } from '@/Components/Toast';
import { ConfirmProvider } from '@/Components/ConfirmDialog';

// Configure Inertia to send CSRF token with every request
router.on('before', (event) => {
    const token = document.head.querySelector('meta[name="csrf-token"]');
    if (token) {
        event.detail.visit.headers = {
            ...event.detail.visit.headers,
            'X-CSRF-TOKEN': token.content
        };
    }
});

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
        createRoot(el).render(
            <ToastProvider>
                <ConfirmProvider>
                    <App {...props} />
                </ConfirmProvider>
            </ToastProvider>
        );
    },
    progress: {
        color: '#E91E8C',
        showSpinner: true,
    },
});

// Update CSRF token meta tag after Inertia page loads
document.addEventListener('inertia:success', (event) => {
    const csrfToken = event.detail.page.props.csrf_token;
    if (csrfToken) {
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        if (metaTag) {
            metaTag.setAttribute('content', csrfToken);
        }
    }
});
