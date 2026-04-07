<template>
  <div class="max-w-md">
    <h1 class="text-3xl font-bold mb-4">Register</h1>

    <form class="card bg-base-100 border p-4 space-y-3" @submit.prevent="onSubmit">
      <label class="form-control">
        <span class="label-text">Pseudo</span>
        <input v-model="pseudo" class="input input-bordered" required />
      </label>

      <label class="form-control">
        <span class="label-text">Email</span>
        <input v-model="email" type="email" class="input input-bordered" required />
      </label>

      <label class="form-control">
        <span class="label-text">Password</span>
        <input v-model="password" type="password" class="input input-bordered" required />
      </label>

      <button class="btn btn-primary w-full" :disabled="loading">
        {{ loading ? "Création..." : "Créer mon compte" }}
      </button>

      <p v-if="error" class="text-error text-sm">{{ error }}</p>
    </form>
  </div>
</template>

<script setup>
import { ref } from "vue";

const pseudo = ref("");
const email = ref("");
const password = ref("");
const loading = ref(false);
const error = ref("");

function onSubmit() {
  error.value = "";
  loading.value = true;

  setTimeout(() => {
    loading.value = false;
    error.value = "";
  }, 400);
}
</script>
