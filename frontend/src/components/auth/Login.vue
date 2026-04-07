<script setup>
import { ref } from "vue";
import LoginView from "../../views/LoginView.vue";
 
const API = import.meta.env.VITE_API_URL;
 
const email = ref("");
const password = ref("");
const loading = ref(false);
const errorMessage = ref("");
 
async function login() {
  errorMessage.value = "";
  loading.value = true;
 
  try {
    const response = await fetch(`${API}/api/login_check`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify({
        email: email.value,
        password: password.value,
      }),
    });
 
    const data = await response.json();
 
    if (!response.ok) {
      errorMessage.value = data.message ?? "Identifiants incorrects.";
      return;
    }
 
    localStorage.setItem("token", data.token);
    console.log("ça marche");
    window.location.href = "/";
 
  } catch (e) {
    errorMessage.value = "Impossible de contacter le serveur.";
  } finally {
    loading.value = false;
  }
}
</script>
<template>
  <LoginView
    v-model:email="email"
    v-model:password="password"
    :loading="loading"
    :error="errorMessage"
    @submit="login"
  />
</template>