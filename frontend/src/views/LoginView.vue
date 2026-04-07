<script setup>
defineProps({
  email: String,
  password: String,
  loading: Boolean,
  error: String,
});

defineEmits(["update:email", "update:password", "submit"]);
</script>

<template>
  <div class="min-h-screen flex items-center justify-center">
    <form class="card bg-base-200 border p-4 space-y-3" @submit.prevent="$emit('submit')">
      <fieldset class="fieldset bg-base-200 rounded-box w-xs">
        <legend class="fieldset-legend">Login</legend>

        <label class="label">Email</label>
        <input :value="email" @input="$emit('update:email', $event.target.value)" type="email"
          class="input input-bordered" placeholder="exemple@mail.com" required />

        <label class="label">Password</label>
        <input :value="password" @input="$emit('update:password', $event.target.value)" type="password"
          class="input input-bordered" placeholder="Votre mot de passe" required />

<button class="btn btn-primary w-full" :disabled="loading" @click="$emit('submit')">
  {{ loading ? "Connexion..." : "Se connecter" }}
</button>
        <p class="text-sm text-center mt-2">
          Pas encore de compte ?
          <RouterLink to="/auth/register" class="link link-primary">S'inscrire</RouterLink>
        </p>

        <p v-if="error" class="text-error text-sm">{{ error }}</p>
      </fieldset>
    </form>
  </div>
</template>