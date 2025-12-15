import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['Accept'] = 'application/json';
window.axios.defaults.withCredentials = true;

// Function to update CSRF token
function updateCSRFToken() {
    const token = document.head.querySelector('meta[name="csrf-token"]');
    if (token) {
        window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
    } else {
        console.error('CSRF token not found');
    }
}

// Set initial CSRF token
updateCSRFToken();

// Update CSRF token on Inertia navigation
document.addEventListener('inertia:success', () => {
    updateCSRFToken();
});
