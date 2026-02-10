<script setup>
import { ref } from 'vue'
import axios from 'axios'

// Reactive form fields
const name = ref('')      
const email = ref('')
const password = ref('')

// Status message
const message = ref('')

// Registration function
const register = async () => {
  message.value = '' // reset message
  try {
    const res = await axios.post('http://localhost:8000/api/register', {
      name: name.value,
      email: email.value,
      password: password.value
    })

    if (res.data.success) {
      message.value = 'Registration successful!'

      // ✅ Reset the form fields
      name.value = ''
      email.value = ''
      password.value = ''
    }
  } catch (err) {
    // Laravel validation or server error
    message.value = err.response?.data?.message || 'Registration failed'
  }
}
</script>

<template>  
  <div class="w-full max-w-sm bg-white rounded-2xl shadow-xl p-7">
    
    <!-- Logo / Title -->
    <div class="text-center mb-6">       
      <h1 class="text-2xl font-extrabold text-black">Register</h1>
      <p class="text-sm text-gray-500">Create an account</p>
    </div>

    <!-- Form -->
    <form class="space-y-4" @submit.prevent="register">

      <!-- Name -->
      <div class="mb-1">
        <label class="block text-sm font-medium text-gray-600 mb-1">
          Name
        </label>
        <input
          type="text"
          v-model="name"
          placeholder="Your Name"
          class="w-full rounded-lg border border-gray-300 px-3 py-2
                 focus:ring-2 focus:ring-blue-500 focus:outline-none"
        />
      </div>

      <!-- Email -->
      <div class="mb-1">
        <label class="block text-sm font-medium text-gray-600 mb-1">
          Email
        </label>
        <input
          type="email"
          v-model="email"
          placeholder="you@example.com"
          class="w-full rounded-lg border border-gray-300 px-3 py-2
                 focus:ring-2 focus:ring-blue-500 focus:outline-none"
        />
      </div>

      <!-- Password -->
      <div class="mb-5">
        <label class="block text-sm font-medium text-gray-600 mb-1">
          Password
        </label>
        <input
          type="password"
          v-model="password"
          placeholder="••••••••"
          class="w-full rounded-lg border border-gray-300 px-3 py-2
                 focus:ring-2 focus:ring-blue-500 focus:outline-none"
        />
      </div>

      <!-- Button -->
      <button
        type="button"
        class="w-full rounded-lg bg-blue-600 py-1.5 font-semibold text-white
               hover:bg-blue-700 transition"
        @click="register"
      >
        Register
      </button>

      <!-- Message -->
      <p v-if="message" class="mt-2 text-center text-sm text-green-600">
        {{ message }}
      </p>

    </form>

    <!-- Footer -->
    <p class="mt-6 text-center text-sm text-gray-500">
      Already have an account?
      <RouterLink to="/" class="font-medium text-blue-600 hover:underline">
        Login
      </RouterLink>
    </p>

  </div>
</template>

<style scoped>
/* Optional additional styling */
</style>
