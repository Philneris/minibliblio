<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Navbar -->
    <nav class="bg-white border-b border-gray-200 shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <h1 class="text-xl font-bold text-indigo-600">📚 MiniBiblio</h1>
          </div>
          <div class="flex items-center space-x-1">
            <router-link
              to="/books"
              class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
              :class="$route.name === 'books' ? 'bg-indigo-100 text-indigo-700' : 'text-gray-600 hover:bg-gray-100'"
            >
              Livres
            </router-link>
            <router-link
              to="/members"
              class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
              :class="$route.name === 'members' ? 'bg-indigo-100 text-indigo-700' : 'text-gray-600 hover:bg-gray-100'"
            >
              Membres
            </router-link>
            <router-link
              to="/loans"
              class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
              :class="$route.name === 'loans' ? 'bg-indigo-100 text-indigo-700' : 'text-gray-600 hover:bg-gray-100'"
            >
              Emprunts
            </router-link>
          </div>
        </div>
      </div>
    </nav>

    <!-- Contenu principal -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <router-view />
    </main>
  </div>
</template>

<script setup>
</script>
