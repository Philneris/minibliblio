<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold text-gray-800">Emprunts en cours</h2>
      <button
        @click="showForm = true"
        class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors"
      >
        + Nouvel emprunt
      </button>
    </div>

    <!-- Message d'erreur global -->
    <div v-if="error" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
      {{ error }}
    </div>

    <!-- Message de succès -->
    <div v-if="success" class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm">
      {{ success }}
    </div>

    <!-- Formulaire d'emprunt -->
    <div v-if="showForm" class="mb-6 bg-white rounded-lg border border-gray-200 shadow-sm p-6">
      <h3 class="text-lg font-semibold text-gray-800 mb-4">Nouvel emprunt</h3>

      <div v-if="formErrors" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
        <ul class="list-disc list-inside">
          <li v-for="(messages, field) in formErrors" :key="field">
            {{ messages[0] }}
          </li>
        </ul>
      </div>

      <form @submit.prevent="submitLoan" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Membre</label>
          <select
            v-model="loanForm.member_id"
            required
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
          >
            <option value="">-- Sélectionner un membre --</option>
            <option v-for="member in members" :key="member.id" :value="member.id">
              {{ member.name }} ({{ member.email }})
            </option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Livre</label>
          <select
            v-model="loanForm.book_id"
            required
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
          >
            <option value="">-- Sélectionner un livre --</option>
            <option
              v-for="book in books"
              :key="book.id"
              :value="book.id"
              :disabled="book.available_copies <= 0"
            >
              {{ book.title }} — {{ book.author }} ({{ book.available_copies }} dispo.)
            </option>
          </select>
        </div>
        <div class="md:col-span-2 flex gap-2">
          <button
            type="submit"
            :disabled="saving"
            class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors disabled:opacity-50"
          >
            {{ saving ? 'Enregistrement...' : 'Emprunter' }}
          </button>
          <button
            type="button"
            @click="cancelForm"
            class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-200 transition-colors"
          >
            Annuler
          </button>
        </div>
      </form>
    </div>

    <!-- Filtre par membre -->
    <div class="mb-4">
      <select
        v-model="filterMemberId"
        @change="fetchLoans"
        class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
      >
        <option value="">Tous les membres</option>
        <option v-for="member in members" :key="member.id" :value="member.id">
          {{ member.name }}
        </option>
      </select>
    </div>

    <!-- État de chargement -->
    <div v-if="loading" class="text-center py-12 text-gray-500">
      Chargement des emprunts...
    </div>

    <!-- Tableau des emprunts -->
    <div v-else-if="loans.length" class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr>
            <th class="text-left px-4 py-3 font-medium text-gray-600">Membre</th>
            <th class="text-left px-4 py-3 font-medium text-gray-600">Livre</th>
            <th class="text-center px-4 py-3 font-medium text-gray-600">Date d'emprunt</th>
            <th class="text-center px-4 py-3 font-medium text-gray-600">Retour prévu</th>
            <th class="text-center px-4 py-3 font-medium text-gray-600">Statut</th>
            <th class="text-right px-4 py-3 font-medium text-gray-600">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="loan in loans" :key="loan.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 font-medium text-gray-800">{{ loan.member?.name }}</td>
            <td class="px-4 py-3 text-gray-600">{{ loan.book?.title }}</td>
            <td class="px-4 py-3 text-center text-gray-500">{{ formatDate(loan.loan_date) }}</td>
            <td class="px-4 py-3 text-center text-gray-500">{{ formatDate(loan.expected_return_date) }}</td>
            <td class="px-4 py-3 text-center">
              <span
                v-if="loan.is_overdue"
                class="inline-block px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700"
              >
                En retard
              </span>
              <span
                v-else
                class="inline-block px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700"
              >
                En cours
              </span>
            </td>
            <td class="px-4 py-3 text-right">
              <button
                @click="returnBook(loan)"
                :disabled="returning === loan.id"
                class="bg-emerald-600 text-white px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-emerald-700 transition-colors disabled:opacity-50"
              >
                {{ returning === loan.id ? 'Retour...' : 'Retourner' }}
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Aucun emprunt -->
    <div v-else class="text-center py-12 text-gray-500">
      Aucun emprunt en cours.
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../api'

const loans = ref([])
const members = ref([])
const books = ref([])
const loading = ref(true)
const error = ref(null)
const success = ref(null)
const showForm = ref(false)
const saving = ref(false)
const returning = ref(null)
const formErrors = ref(null)
const filterMemberId = ref('')

const loanForm = ref({
  member_id: '',
  book_id: '',
})

function formatDate(dateStr) {
  if (!dateStr) return '-'
  const date = new Date(dateStr)
  return date.toLocaleDateString('fr-FR')
}

async function fetchLoans() {
  loading.value = true
  error.value = null
  try {
    const params = {}
    if (filterMemberId.value) {
      params.member_id = filterMemberId.value
    }
    const response = await api.get('/loans', { params })
    loans.value = response.data
  } catch (err) {
    error.value = 'Erreur lors du chargement des emprunts.'
  } finally {
    loading.value = false
  }
}

async function fetchMembers() {
  try {
    const response = await api.get('/members')
    members.value = response.data
  } catch (err) {
    // silencieux
  }
}

async function fetchBooks() {
  try {
    const response = await api.get('/books')
    books.value = response.data
  } catch (err) {
    // silencieux
  }
}

function cancelForm() {
  showForm.value = false
  formErrors.value = null
  loanForm.value = { member_id: '', book_id: '' }
}

async function submitLoan() {
  saving.value = true
  formErrors.value = null
  error.value = null
  success.value = null
  try {
    await api.post('/loans', loanForm.value)
    cancelForm()
    success.value = 'Emprunt enregistré avec succès.'
    await Promise.all([fetchLoans(), fetchBooks()])
    setTimeout(() => { success.value = null }, 3000)
  } catch (err) {
    if (err.response && err.response.status === 422) {
      formErrors.value = err.response.data.errors
    } else {
      formErrors.value = { general: ['Une erreur est survenue.'] }
    }
  } finally {
    saving.value = false
  }
}

async function returnBook(loan) {
  returning.value = loan.id
  error.value = null
  success.value = null
  try {
    await api.patch(`/loans/${loan.id}/return`)
    success.value = 'Livre retourné avec succès.'
    await Promise.all([fetchLoans(), fetchBooks()])
    setTimeout(() => { success.value = null }, 3000)
  } catch (err) {
    if (err.response && err.response.data.errors) {
      error.value = Object.values(err.response.data.errors).flat().join(' ')
    } else {
      error.value = 'Erreur lors du retour.'
    }
  } finally {
    returning.value = null
  }
}

onMounted(async () => {
  await Promise.all([fetchLoans(), fetchMembers(), fetchBooks()])
})
</script>
