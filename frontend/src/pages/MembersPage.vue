<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold text-gray-800">Membres</h2>
      <button
        @click="showForm = true"
        class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors"
      >
        + Ajouter un membre
      </button>
    </div>

    <!-- Message d'erreur global -->
    <div v-if="error" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
      {{ error }}
    </div>

    <!-- Formulaire de création / modification -->
    <div v-if="showForm" class="mb-6 bg-white rounded-lg border border-gray-200 shadow-sm p-6">
      <h3 class="text-lg font-semibold text-gray-800 mb-4">
        {{ editingMember ? 'Modifier le membre' : 'Ajouter un membre' }}
      </h3>

      <div v-if="formErrors" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
        <ul class="list-disc list-inside">
          <li v-for="(messages, field) in formErrors" :key="field">
            {{ messages[0] }}
          </li>
        </ul>
      </div>

      <form @submit.prevent="submitForm" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
          <input
            v-model="form.name"
            type="text"
            required
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
            placeholder="Nom complet"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
          <input
            v-model="form.email"
            type="email"
            required
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
            placeholder="email@exemple.com"
          />
        </div>
        <div class="md:col-span-2 flex gap-2">
          <button
            type="submit"
            :disabled="saving"
            class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors disabled:opacity-50"
          >
            {{ saving ? 'Enregistrement...' : (editingMember ? 'Modifier' : 'Ajouter') }}
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

    <!-- État de chargement -->
    <div v-if="loading" class="text-center py-12 text-gray-500">
      Chargement des membres...
    </div>

    <!-- Tableau des membres -->
    <div v-else-if="members.length" class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr>
            <th class="text-left px-4 py-3 font-medium text-gray-600">Nom</th>
            <th class="text-left px-4 py-3 font-medium text-gray-600">Email</th>
            <th class="text-center px-4 py-3 font-medium text-gray-600">Emprunts en cours</th>
            <th class="text-right px-4 py-3 font-medium text-gray-600">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="member in members" :key="member.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 font-medium text-gray-800">{{ member.name }}</td>
            <td class="px-4 py-3 text-gray-600">{{ member.email }}</td>
            <td class="px-4 py-3 text-center">
              <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                {{ member.active_loans_count }}
              </span>
            </td>
            <td class="px-4 py-3 text-right">
              <button
                @click="editMember(member)"
                class="text-indigo-600 hover:text-indigo-800 text-sm font-medium mr-3"
              >
                Modifier
              </button>
              <button
                @click="deleteMember(member)"
                class="text-red-600 hover:text-red-800 text-sm font-medium"
              >
                Supprimer
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Aucun membre -->
    <div v-else class="text-center py-12 text-gray-500">
      Aucun membre enregistré.
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../api'

const members = ref([])
const loading = ref(true)
const error = ref(null)
const showForm = ref(false)
const saving = ref(false)
const formErrors = ref(null)
const editingMember = ref(null)

const form = ref({
  name: '',
  email: '',
})

async function fetchMembers() {
  loading.value = true
  error.value = null
  try {
    const response = await api.get('/members')
    members.value = response.data
  } catch (err) {
    error.value = 'Erreur lors du chargement des membres.'
  } finally {
    loading.value = false
  }
}

function editMember(member) {
  editingMember.value = member
  form.value = {
    name: member.name,
    email: member.email,
  }
  formErrors.value = null
  showForm.value = true
}

function cancelForm() {
  showForm.value = false
  editingMember.value = null
  formErrors.value = null
  form.value = { name: '', email: '' }
}

async function submitForm() {
  saving.value = true
  formErrors.value = null
  try {
    if (editingMember.value) {
      await api.put(`/members/${editingMember.value.id}`, form.value)
    } else {
      await api.post('/members', form.value)
    }
    cancelForm()
    await fetchMembers()
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

async function deleteMember(member) {
  if (!confirm(`Supprimer "${member.name}" ?`)) return
  error.value = null
  try {
    await api.delete(`/members/${member.id}`)
    await fetchMembers()
  } catch (err) {
    if (err.response && err.response.data.errors) {
      error.value = Object.values(err.response.data.errors).flat().join(' ')
    } else if (err.response && err.response.data.message) {
      error.value = err.response.data.message
    } else {
      error.value = 'Erreur lors de la suppression.'
    }
  }
}

onMounted(fetchMembers)
</script>
