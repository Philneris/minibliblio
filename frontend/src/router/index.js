import { createRouter, createWebHistory } from 'vue-router'

import BooksPage from '../pages/BooksPage.vue'
import MembersPage from '../pages/MembersPage.vue'
import LoansPage from '../pages/LoansPage.vue'

const routes = [
  { path: '/', redirect: '/books' },
  { path: '/books', name: 'books', component: BooksPage },
  { path: '/members', name: 'members', component: MembersPage },
  { path: '/loans', name: 'loans', component: LoansPage },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router
