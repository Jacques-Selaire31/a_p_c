// Ici on va créer une instance de router et config des routes
import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(),
  routes: [

{
    path: '/',
    name: 'home',
    component: () => import('../views/HomeView.vue'),
},
    {
      path: '/write',
      name: 'write',
      component: () => import('../components/CreateArticle.vue'),
    },
    // {
    //   path: '/article',
    //   name: 'article',
    //   component: () => import('../components/Article.vue'),
    // },
    {
      path: '/auth/login',
      name: 'login',
      component: () => import('../components/auth/Login.vue'),
    },
    {
      path: '/auth/register',
      name: 'register',
      component: () => import('../components/auth/Register.vue'),
    },
        {
      path: '/article',
      name: 'article',
      component: () => import('../components/ArticleCard.vue'),
    },
{ path: '/article/:slug', name: 'article', component: () => import('../views/ArticleView.vue') }
    // {
    //   path: '/account',
    //   name: 'account',
    //   component: () => import('../components/Account.vue'),
    // },
    //   {
    //     path: '/:pathMatch(.*)*',
    //     name: 'NotFound',
    //     component: () => import('../views/NotFoundView.vue')
    // },
  ],
})

export default router