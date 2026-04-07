<template>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary/5 to-secondary/10">
        <div class="card w-full max-w-md shadow-2xl bg-base-100">
            <div class="card-body">
                <h1 class="text-3xl font-bold text-center text-primary mb-6">Inscription :</h1>

                <div v-if="errorMessage" class="alert alert-error shadow-lg mb-4">
                    <span>{{ errorMessage }}</span>
                </div>

                <div v-if="successMessage" class="alert alert-success shadow-lg mb-4">
                    <span>{{ successMessage }}</span>
                </div>

                <form @submit.prevent="handleRegister" class="space-y-4">
                    <!-- Si ton back n'a pas de displayName, tu peux supprimer ce champ -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Nom affiché</span>
                        </label>
                        <input v-model="pseudo" type="text" placeholder="Votre pseudo"
                            class="input input-bordered w-full" />
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Email</span>
                        </label>
                        <input v-model="email" type="email" placeholder="email@exemple.com"
                            class="input input-bordered w-full" />
                    </div>
                    <label class="label">
                        <span class="label-text font-medium">Date de naissance</span>
                    </label>
                    <input v-model="dob" type="date" class="input input-bordered w-full" required />

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Mot de passe</span>
                        </label>
                        <input v-model="password" type="password" placeholder="********"
                            class="input input-bordered w-full" />
                    </div>

                    <button class="btn btn-primary w-full" :disabled="loading">
                        <span v-if="loading" class="loading loading-spinner"></span>
                        <span v-else>S’inscrire</span>
                    </button>
                </form>

                <p class="text-center mt-4">
                    Déjà un compte ?
                    <router-link to="/auth/login" class="link link-primary">Se connecter</router-link>
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";

const API = import.meta.env.VITE_API_URL;

const pseudo = ref("");
const email = ref("");
const password = ref("");
const dob = ref("");
const loading = ref(false);
const errorMessage = ref("");
const successMessage = ref("");

const router = useRouter();

async function handleRegister() {
    errorMessage.value = "";
    successMessage.value = "";
    loading.value = true;

    try {
        const payload = {
            email: email.value,
            password: password.value,
            // Si ton back ne gère pas displayName, tu peux enlever la ligne suivante
            pseudo: pseudo.value,
            dob: dob.value,
        };

        const response = await fetch(`${API}/api/user/register`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
            },
            body: JSON.stringify(payload),
        });

        const contentType = response.headers.get("content-type") || "";
        const data = contentType.includes("application/json")
            ? await response.json()
            : { message: await response.text() };

        if (!response.ok) {
            errorMessage.value = data.message || data.detail || "Inscription impossible";
            return;
        }

        successMessage.value = "Compte créé ! Redirection vers la connexion…";
        setTimeout(() => router.push("/auth/login"), 600);
    } catch (e) {
        errorMessage.value = "Erreur réseau (API inaccessible)";
    } finally {
        loading.value = false;
    }
}
</script>
