import { createRouter, createWebHistory } from 'vue-router'
import Login from '../views/Login.vue'
import Register from '../views/Register.vue'
import Home from '../views/Home.vue' // your home page

const routes = [
  {
    path: '/',
    name: 'login',
    component: Login,
    meta: { guest: true } // guest-only
  },
  {
    path: '/register',
    name: 'register',
    component: Register,
    meta: { guest: true } // guest-only
  },
  {
    path: '/home',
    name: 'home',
    component: Home,
    meta: { requiresAuth: true } // protected route
  }
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes
})

// ✅ Navigation guard
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token') // check Sanctum token

  if (to.meta.requiresAuth && !token) {
    // Not logged in, redirect to login
    next({ name: 'login' })
  } else if (to.meta.guest && token) {
    // Logged in user trying to access login/register
    next({ name: 'home' })
  } else {
    next() // proceed normally
  }
})

export default router
