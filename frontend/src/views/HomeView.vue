<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';

const API = import.meta.env.VITE_API_URL;
const router = useRouter();
const articles = ref([]);
const error = ref('');
const loading = ref(true);

onMounted(async () => {
    try {
        const response = await fetch(`${API}/api/public/articles`);
        if (!response.ok) throw new Error('Erreur chargement articles');
        articles.value = await response.json();
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <div class="max-w-3xl mx-auto px-4 py-10">
        <div v-if="loading" class="text-center">Chargement...</div>
        <div v-else-if="error" class="text-error">{{ error }}</div>
        <div v-else class="space-y-6">
            <div v-for="article in articles" :key="article.id"
                class="card bg-base-100 border p-6 cursor-pointer hover:shadow-md transition"
                @click="router.push({ name: 'article', params: { slug: article.slug } })">
                <h2 class="text-xl font-bold mb-1">{{ article.title }}</h2>
                <img v-if="article.coverImage" :src="article.coverImage" :alt="article.title"
                    class="w-full h-48 object-cover rounded-t-lg mb-4" />
                <p v-if="article.textPreview" class="text-gray-400 text-sm mb-3">
                    {{ article.textPreview }}
                </p>

                <p class="text-sm text-gray-500 mb-3">
                    Par {{ article.author.pseudo }} — {{ article.createdAt }}
                </p>
                <div class="flex gap-2 flex-wrap">
                    <span v-for="category in article.categories" :key="category.id" class="badge badge-outline">
                        {{ category.name }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>