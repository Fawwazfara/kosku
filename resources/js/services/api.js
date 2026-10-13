import axios from 'axios';

const api = axios.create({
    baseURL: '/api',
    withCredentials: true,
    withXSRFToken: true,
    headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
});

let csrfReady = null;

export function ensureCsrf() {
    if (!csrfReady) {
        csrfReady = axios.get('/sanctum/csrf-cookie', { withCredentials: true }).catch(() => null);
    }
    return csrfReady;
}

api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 419) {
            csrfReady = null;
        }
        return Promise.reject(error);
    },
);

export default api;
