import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const routes = [
    {
        path: '/',
        name: 'home',
        component: () => import('../pages/HomePage.vue'),
    },
    {
        path: '/kos/:slug',
        name: 'kos.detail',
        component: () => import('../pages/KosDetailPage.vue'),
    },
    {
        path: '/masuk',
        name: 'login',
        component: () => import('../pages/LoginPage.vue'),
        meta: { guestOnly: true },
    },
    {
        path: '/daftar',
        name: 'register',
        component: () => import('../pages/RegisterPage.vue'),
        meta: { guestOnly: true },
    },
    {
        path: '/favorit',
        name: 'favorites',
        component: () => import('../pages/FavoritesPage.vue'),
        meta: { requiresAuth: true },
    },
    {
        path: '/pemilik',
        name: 'owner.dashboard',
        component: () => import('../pages/owner/OwnerDashboardPage.vue'),
        meta: { requiresAuth: true, roles: ['owner', 'admin'] },
    },
    {
        path: '/pemilik/kos/baru',
        name: 'owner.kos.create',
        component: () => import('../pages/owner/KosFormPage.vue'),
        meta: { requiresAuth: true, roles: ['owner', 'admin'] },
    },
    {
        path: '/pemilik/kos/:id',
        name: 'owner.kos.edit',
        component: () => import('../pages/owner/KosFormPage.vue'),
        meta: { requiresAuth: true, roles: ['owner', 'admin'] },
    },
    {
        path: '/admin',
        name: 'admin.dashboard',
        component: () => import('../pages/admin/AdminDashboardPage.vue'),
        meta: { requiresAuth: true, roles: ['admin'] },
    },
    {
        path: '/admin/pemilik',
        name: 'admin.owners',
        component: () => import('../pages/admin/AdminOwnersPage.vue'),
        meta: { requiresAuth: true, roles: ['admin'] },
    },
    {
        path: '/admin/kos',
        name: 'admin.kos',
        component: () => import('../pages/admin/AdminKosPage.vue'),
        meta: { requiresAuth: true, roles: ['admin'] },
    },
    {
        path: '/admin/ulasan',
        name: 'admin.reviews',
        component: () => import('../pages/admin/AdminReviewsPage.vue'),
        meta: { requiresAuth: true, roles: ['admin'] },
    },
    {
        path: '/admin/fasilitas',
        name: 'admin.facilities',
        component: () => import('../pages/admin/AdminFacilitiesPage.vue'),
        meta: { requiresAuth: true, roles: ['admin'] },
    },
    {
        path: '/admin/pengguna',
        name: 'admin.users',
        component: () => import('../pages/admin/AdminUsersPage.vue'),
        meta: { requiresAuth: true, roles: ['admin'] },
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'not-found',
        component: () => import('../pages/NotFoundPage.vue'),
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior: () => ({ top: 0 }),
});

router.beforeEach(async (to) => {
    const auth = useAuthStore();

    if (!auth.booted) {
        await auth.fetchUser();
    }

    if (to.meta.requiresAuth && !auth.isLoggedIn) {
        return { name: 'login', query: { redirect: to.fullPath } };
    }

    if (to.meta.guestOnly && auth.isLoggedIn) {
        return { name: 'home' };
    }

    if (to.meta.roles && !to.meta.roles.includes(auth.user?.role)) {
        return { name: 'home' };
    }

    return true;
});

export default router;
