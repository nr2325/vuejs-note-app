<script setup>
import { ref, onMounted, nextTick } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

// Router
const router = useRouter()

// Notes list
const notes = ref([])

// Status message
const message = ref('')
const messageType = ref('success') // 'success' | 'error'

// Modal and form
const showModal = ref(false)
const isEditing = ref(false) // true for edit, false for add
const currentNote = ref({ id: null, title: '', text: '' })

// Loading state
const loading = ref(false)

// ----------------------
// Set Authorization token
// ----------------------
const setAuthToken = () => {
  const token = localStorage.getItem('token')
  if (token) axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
  else delete axios.defaults.headers.common['Authorization']
}

// ----------------------
// Show status message
// ----------------------
const showMessage = (msg, type = 'success') => {
  message.value = msg
  messageType.value = type
  setTimeout(() => (message.value = ''), 3000)
}

// ----------------------
// Fetch all notes
// ----------------------
const fetchNotes = async () => {
  try {
    setAuthToken()
    const res = await axios.get('http://localhost:8000/api/notes')
    notes.value = res.data.notes || []
  } catch (err) {
    if (err.response?.status === 401) logout()
    else showMessage(err.response?.data?.message || 'Failed to fetch notes', 'error')
  }
}

// ----------------------
// Open modal for Add/Edit
// ----------------------
const openModal = (note = null) => {
  if (note) {
    isEditing.value = true
    currentNote.value = { ...note }
  } else {
    isEditing.value = false
    currentNote.value = { id: null, title: '', text: '' }
  }
  showModal.value = true
  nextTick(() => document.getElementById('note-title')?.focus())
}

// ----------------------
// Save note (Add or Edit)
// ----------------------
const saveNote = async () => {
  if (!currentNote.value.title.trim() || !currentNote.value.text.trim()) {
    return showMessage('Title and text cannot be empty', 'error')
  }

  try {
    loading.value = true
    setAuthToken()

    if (isEditing.value) {
      // Update note
      const res = await axios.put(
        `http://localhost:8000/api/notes/${currentNote.value.id}`,
        {
          title: currentNote.value.title,
          text: currentNote.value.text,
        }
      )
      const index = notes.value.findIndex(n => n.id === currentNote.value.id)
      if (index !== -1) notes.value[index] = res.data.note
      showMessage('Note updated')
    } else {
      // Add new note
      const res = await axios.post('http://localhost:8000/api/notes', {
        title: currentNote.value.title,
        text: currentNote.value.text,
      })
      notes.value.push(res.data.note)
      showMessage('Note added')
    }

    showModal.value = false
    currentNote.value = { id: null, title: '', text: '' }
  } catch (err) {
    showMessage(err.response?.data?.message || 'Operation failed', 'error')
  } finally {
    loading.value = false
  }
}

// ----------------------
// Delete note
// ----------------------
const deleteNote = async (noteId) => {
  if (!confirm('Are you sure you want to delete this note?')) return
  try {
    setAuthToken()
    await axios.delete(`http://localhost:8000/api/notes/${noteId}`)
    notes.value = notes.value.filter(n => n.id !== noteId)
    showMessage('Note deleted')
  } catch (err) {
    showMessage(err.response?.data?.message || 'Delete failed', 'error')
  }
}

// ----------------------
// Logout
// ----------------------
const logout = async () => {
  try {
    setAuthToken()
    await axios.post('http://localhost:8000/api/logout')
  } catch (_) {}
  localStorage.removeItem('token')
  delete axios.defaults.headers.common['Authorization']
  router.push({ name: 'login' })
}

// Fetch notes on mount
onMounted(fetchNotes)
</script>

<template>
  <div class="min-h-screen bg-gray-100 p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">My Notes</h1>
      <div class="flex items-center gap-4">
        <button
          @click="openModal()"
          class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition"
        >
          Add Note
        </button>
        <button
          @click="logout"
          class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition"
        >
          Logout
        </button>
      </div>
    </div>

    <!-- Status message -->
    <p
      v-if="message"
      class="mb-4"
      :class="messageType === 'success' ? 'text-green-600' : 'text-red-600'"
    >
      {{ message }}
    </p>

    <!-- Notes Table -->
    <div class="overflow-x-auto bg-white shadow rounded-lg">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Note Text</th>
            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="note in notes" :key="note.id">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ note.id }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ note.title }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ note.text }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
              <button
                @click="openModal(note)"
                class="px-2 py-1 bg-yellow-400 text-white rounded hover:bg-yellow-500 transition text-sm mr-2"
              >
                Edit
              </button>
              <button
                @click="deleteNote(note.id)"
                class="px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600 transition text-sm"
              >
                Delete
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <p v-if="notes.length === 0" class="mt-6 text-center text-gray-500">No notes yet.</p>

    <!-- Add/Edit Note Modal -->
    <div v-if="showModal" class="fixed inset-0 flex items-center justify-center z-50 bg-black/30">
      <div class="bg-white rounded-lg shadow-lg w-96 p-6 relative">
        <h2 class="text-xl font-bold mb-4">{{ isEditing ? 'Edit Note' : 'Add New Note' }}</h2>
        <input
          id="note-title"
          v-model="currentNote.title"
          type="text"
          placeholder="Enter note title"
          class="w-full px-4 py-2 border rounded mb-4"
        />
        <textarea
          v-model="currentNote.text"
          rows="4"
          placeholder="Enter note text"
          class="w-full px-4 py-2 border rounded mb-4"
        ></textarea>
        <div class="flex justify-end gap-2">
          <button
            @click="showModal = false"
            class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition"
          >
            Cancel
          </button>
          <button
            @click="saveNote"
            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition"
            :disabled="loading"
          >
            {{ isEditing ? 'Update' : 'Add' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
table tbody tr:hover {
  background-color: #f9fafb;
}
</style>
