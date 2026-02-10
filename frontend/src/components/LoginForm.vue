<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

// Router
const router = useRouter()

// Form fields
const email = ref('')
const password = ref('')

// Message + status
const message = ref('')
const isSuccess = ref(false)

// Login function
const login = async () => {
  message.value = ''
  isSuccess.value = false

  try {
    const res = await axios.post('http://localhost:8000/api/login', {
      email: email.value,
      password: password.value
    })

    if (res.data.success) {
      message.value = 'Login successful!'
      isSuccess.value = true

      // Store token
      if (res.data.token) {
        localStorage.setItem('token', res.data.token)
        axios.defaults.headers.common['Authorization'] =
          `Bearer ${res.data.token}`
      }

      // Reset form
      email.value = ''
      password.value = ''

      // ⏳ Redirect after 1500 ms
      setTimeout(() => {
        router.push('/home')
      }, 1500)
    }
  } catch (err) {
    message.value = err.response?.data?.message || 'Login failed'
    isSuccess.value = false
  }
}
</script>

<template>
  <div class="w-full max-w-sm bg-white rounded-2xl shadow-xl p-7">

    <!-- Title -->
    <div class="text-center mb-6">
      <h1 class="text-2xl font-extrabold text-black">Login</h1>
      <p class="text-sm text-gray-500">Login to continue</p>
    </div>

    <!-- Form -->
    <form class="space-y-4" @submit.prevent="login">

      <!-- Email -->
      <div>
        <label class="block text-sm font-medium text-gray-600 mb-1">
          Email
        </label>
        <input
          type="email"
          v-model="email"
          placeholder="Enter your email"
          class="w-full rounded-lg border border-gray-300 px-3 py-2
                 focus:ring-2 focus:ring-blue-500 focus:outline-none"
        />
      </div>

      <!-- Password -->
      <div>
        <label class="block text-sm font-medium text-gray-600 mb-1">
          Password
        </label>
        <input
          type="password"
          v-model="password"
          placeholder="Enter your password"
          class="w-full rounded-lg border border-gray-300 px-3 py-2
                 focus:ring-2 focus:ring-blue-500 focus:outline-none"
        />
      </div>

      <!-- Button -->
      <button
        type="submit"
        class="w-full rounded-lg bg-blue-600 py-1.5 font-semibold text-white
               hover:bg-blue-700 transition"
      >
        Login
      </button>

      <!-- Message -->
      <p
        v-if="message"
        class="text-center text-sm font-medium"
        :class="isSuccess ? 'text-green-600' : 'text-red-600'"
      >
        {{ message }}
      </p>

    </form>

    <!-- Footer -->
    <p class="mt-6 text-center text-sm text-gray-500">
      Don’t have an account?
      <RouterLink
        to="/register"
        class="font-medium text-blue-600 hover:underline"
      >
        Register
      </RouterLink>
    </p>

  </div>
</template>

<style scoped>
/* optional */
</style>
