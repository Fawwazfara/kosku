import { defineStore } from 'pinia';
import api, { ensureCsrf } from '../services/api';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        loading: false,
        booted: false,
    }),

    getters: {
        isLoggedIn: (state) => !!state.user,
        isAdmin: (state) => state.user?.role === 'admin',
        isOwner: (state) => state.user?.role === 'owner',
        isStudent: (state) => state.user?.role === 'student',
        isVerifiedOwner: (state) => state.user?.role === 'owner' && state.user?.is_verified,
        ownerPending: (state) => state.user?.role === 'owner' && !state.user?.is_verified,
    },

    actions: {
        async fetchUser() {
            try {
                const { data } = await api.get('/user');
                this.user = data.data;
            } catch {
                this.user = null;
            } finally {
                this.booted = true;
            }
        },

        async login(payload) {
            await ensureCsrf();
            const { data } = await api.post('/login', payload);
            this.user = data.user;
            return data;
        },

        async register(payload) {
            await ensureCsrf();
            const { data } = await api.post('/register', payload);
            this.user = data.user;
            return data;
        },

        async logout() {
            try {
                await api.post('/logout');
            } finally {
                this.user = null;
            }
        },
    },
});
