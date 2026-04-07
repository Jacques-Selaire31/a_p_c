<script setup>
import { SquarePen, Bell, Search } from "lucide-vue-next"
import HeaderCalendar from "./HeaderCalendar.vue"
// import { useProfile } from "../composables/useProfil"

// const { profile } = useProfile()

async function logout() {
  await signOut()
  window.location.href = "/auth/login"
}
</script>

<template>

  <!-- Gauche -->
<div class="navbar fixed top-0 left-0 right-0 w-full shadow-sm z-50 px-6"
     style="background-color: #282828">

  <div class="navbar-start">
    <RouterLink to="/" class="text-white font-bold">
      POINT DE CORDE
    </RouterLink>

    <label
      class="hidden md:flex
             ml-4
             flex items-center gap-2
             bg-white/90
             px-4 py-1.5
             rounded-full
             w-56"
    >
      <Search class="w-4 h-4 text-gray-500" />
      <input
        type="text"
        class="bg-transparent outline-none text-sm w-full
               text-gray-700
               placeholder:text-gray-400"
        placeholder="Rechercher..."
      />
    </label>
  </div>
    <!-- Droite -->
    <div class="navbar-end hidden md:flex items-center gap-4 text-white">
      <header-calendar />
      <RouterLink to="/write">
        <button id="write-btn" class="btn btn-link flex items-center gap-2 text-white">
          <SquarePen class="w-5 h-5" /><span>Rédiger</span>
        </button>
      </RouterLink>

      <div class="indicator">
        <button class="btn btn-ghost btn-circle text-white">
          <Bell class="w-5 h-5" />
        </button>
        <!-- <span class="badge badge-xs badge-error indicator-item text-white">5</span> -->
      </div>

      <!-- si user est connecté -->
      <template v-if="user">
        <div class="dropdown dropdown-end">
          <div tabindex="0" role="button" class="btn btn-ghost btn-circle avatar">
            <!-- <UserAvatar :src="profile?.avatar_url" :alt="profile?.username || user?.email" :size="40" -->
            class="rounded-full border border-white" />
          </div>
          <ul tabindex="0" class="menu menu-sm dropdown-content bg-base-100 rounded-box mt-3 w-52 p-2 shadow">
            <li>
              <RouterLink to="/account">Profil</RouterLink>
            </li>
            <li><a>Paramètres</a></li>
            <li><a @click.prevent="logout">Se déconnecter</a></li>
          </ul>
        </div>
      </template>

      <!-- si pas connecté -->
      <template v-else>
        <RouterLink to="/auth/login">
          <button class="btn btn-primary btn-sm">Se connecter</button>
        </RouterLink>
      </template>
    </div>
  </div>
</template>
