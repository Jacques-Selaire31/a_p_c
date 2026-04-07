<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';


const API = import.meta.env.VITE_API_URL;
const route = useRoute();


// REF
const article = ref(null);
const comments = ref([]);
const loading = ref(true);
const error = ref('');

// Formulaire commentaire
const newComment = ref('');
const posting = ref(false);
const postError = ref('');

const token = localStorage.getItem('token');
const isLogged = !!token;

// Sequentielle pour éviter undefined
onMounted(async () => {
    await fetchArticle();
    await fetchComments();
    loading.value = false;
});

async function fetchArticle() {
    try {
        const response = await fetch(`${API}/api/public/articles/${route.params.slug}`);
        if (!response.ok) throw new Error('Article introuvable');
        article.value = await response.json();
    } catch (e) {
        error.value = e.message;
    }
}

async function fetchComments() {
    try {
        const response = await fetch(`${API}/api/public/article/${article.value.id}/comments`);
        if (!response.ok) throw new Error('Erreur chargement commentaires');
        comments.value = await response.json();
    } catch (e) {
        error.value = e.message;
    }
}

async function postComment() {
    if (!newComment.value.trim()) return;
    posting.value = true;
    postError.value = '';

    try {
        const response = await fetch(`${API}/api/comment`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`,
            },
            body: JSON.stringify({
                articleId: article.value.id,
                content: newComment.value,
            }),
        });

        if (!response.ok) {
            const data = await response.json();
            throw new Error(data.message ?? 'Erreur lors de l\'envoi');
        }

        newComment.value = '';
        await fetchComments(); // Recharge les commentaires

    } catch (e) {
        postError.value = e.message;
    } finally {
        posting.value = false;
    }
}

// Réponse à un commentaire
const replyingTo = ref(null); // { id, author } du commentaire auquel on répond
const replyContent = ref('');
const replyPosting = ref(false);
const replyError = ref('');

function openReply(comment) {
    replyingTo.value = { id: comment.id, author: comment.author };
    replyContent.value = '';
    replyError.value = '';
}

function closeReply() {
    replyingTo.value = null;
    replyContent.value = '';
}

async function postReply() {
    if (!replyContent.value.trim()) return;
    replyPosting.value = true;
    replyError.value = '';

    try {
        const response = await fetch(`${API}/api/comment`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`,
            },
            body: JSON.stringify({
                articleId: article.value.id,
                content: replyContent.value,
                parentId: replyingTo.value.id,
            }),
        });

        if (!response.ok) {
            const data = await response.json();
            throw new Error(data.message ?? 'Erreur lors de l\'envoi');
        }

        replyingTo.value = null;
        replyContent.value = '';
        await fetchComments();

    } catch (e) {
        replyError.value = e.message;
    } finally {
        replyPosting.value = false;
    }
}



</script>

<template>
    <div class="max-w-3xl mx-auto px-4 py-10">

        <div v-if="loading" class="text-center">Chargement...</div>
        <div v-else-if="error" class="text-error">{{ error }}</div>

        <article v-else-if="article">

            <!-- Article -->
            <h1 class="text-4xl font-bold mb-2">{{ article.title }}</h1>
            <!-- Après le titre et la date, avant les catégories -->
            <img v-if="article.coverImage" :src="article.coverImage" :alt="article.title"
                class="w-full h-72 object-cover rounded-lg mb-6" />
            <p class="text-sm text-gray-500 mb-6">
                Par {{ article.author.pseudo }} — {{ article.createdAt }}
            </p>

            <div class="flex gap-2 mb-8">
                <span v-for="category in article.categories" :key="category.id" class="badge badge-outline">
                    {{ category.name }}
                </span>
            </div>

            <!-- Contenu Editor.js -->
            <div class="prose max-w-none mb-12">
                <template v-for="block in article.content.blocks" :key="block.id">
                    <p v-if="block.type === 'paragraph'" v-html="block.data.text" />
                    <h2 v-else-if="block.type === 'header' && block.data.level === 2">{{ block.data.text }}</h2>
                    <h3 v-else-if="block.type === 'header' && block.data.level === 3">{{ block.data.text }}</h3>
                    <ul v-else-if="block.type === 'list' && block.data.style === 'unordered'">
                        <li v-for="item in block.data.items" :key="item">{{ item }}</li>
                    </ul>
                    <ol v-else-if="block.type === 'list' && block.data.style === 'ordered'">
                        <li v-for="item in block.data.items" :key="item">{{ item }}</li>
                    </ol>
                    <blockquote v-else-if="block.type === 'quote'">{{ block.data.text }}</blockquote>
                    <hr v-else-if="block.type === 'delimiter'" />
                </template>
            </div>

            <!-- Commentaires -->
            <div class="border-t pt-8">
                <h2 class="text-2xl font-bold mb-6">Commentaires</h2>

                <!-- Formulaire si connecté -->
                <div v-if="isLogged" class="mb-8">
                    <textarea v-model="newComment" class="textarea textarea-bordered w-full mb-2"
                        placeholder="Écrire un commentaire..." rows="3" />
                    <p v-if="postError" class="text-error text-sm mb-2">{{ postError }}</p>
                    <button class="btn btn-primary" :disabled="posting" @click="postComment">
                        {{ posting ? 'Envoi...' : 'Publier' }}
                    </button>
                </div>
                <div v-else class="mb-8 text-gray-500 text-sm">
                    <RouterLink to="/auth/login" class="link link-primary">Connectez-vous</RouterLink>
                    pour laisser un commentaire.
                </div>

                <!-- Liste des commentaires -->
                <div v-if="comments.length === 0" class="text-gray-400">
                    Aucun commentaire pour l'instant.
                </div>
                <div v-else class="space-y-6">
                    <div v-for="comment in comments" :key="comment.id" class="border-b pb-4">
                        <p class="font-semibold">{{ comment.author }}</p>
                        <p class="text-sm text-gray-400 mb-2">{comment.createdAt}</p>
                        <p class="mb-2">{{ comment.content }}</p>

                        <!-- Bouton Reply -->
                        <button v-if="isLogged" class="text-sm text-primary" @click="openReply(comment)">
                            Répondre
                        </button>

                        <!-- Formulaire de réponse -->
                        <div v-if="replyingTo && replyingTo.id === comment.id" class="mt-3">
                            <p class="text-sm text-gray-400 mb-1">Répondre à {{ replyingTo.author }}</p>
                            <textarea v-model="replyContent" class="textarea textarea-bordered w-full mb-2"
                                :placeholder="`@${replyingTo.author} `" rows="2" />
                            <p v-if="replyError" class="text-error text-sm mb-2">{{ replyError }}</p>
                            <div class="flex gap-2">
                                <button class="btn btn-primary btn-sm" :disabled="replyPosting" @click="postReply">
                                    {{ replyPosting ? 'Envoi...' : 'Répondre' }}
                                </button>
                                <button class="btn btn-ghost btn-sm" @click="closeReply">Annuler</button>
                            </div>
                        </div>

                        <!-- Réponses -->
                        <div v-if="comment.replies && comment.replies.length > 0" class="ml-6 mt-4 space-y-4">
                            <div v-for="reply in comment.replies" :key="reply.id" class="border-l-2 pl-4">
                                <p class="font-semibold">{{ reply.author }}</p>
                                <p class="text-sm text-gray-400 mb-1">{{ reply.createdAt }}</p>
                                <p v-if="reply.parent" class="text-sm text-gray-400 italic mb-1">
                                    ↩ {{ reply.parent.author }} : "{{ reply.parent.content }}"
                                </p>
                                <p class="mb-2">{{ reply.content }}</p>

                                <!-- Bouton Reply sur les réponses aussi -->
                                <button v-if="isLogged" class="text-sm text-primary" @click="openReply(reply)">
                                    Répondre
                                </button>

                                <!-- Formulaire de réponse sur les réponses -->
                                <div v-if="replyingTo && replyingTo.id === reply.id" class="mt-3">
                                    <p class="text-sm text-gray-400 mb-1">Répondre à {{ replyingTo.author }}</p>
                                    <textarea v-model="replyContent" class="textarea textarea-bordered w-full mb-2"
                                        :placeholder="`@${replyingTo.author} `" rows="2" />
                                    <p v-if="replyError" class="text-error text-sm mb-2">{{ replyError }}</p>
                                    <div class="flex gap-2">
                                        <button class="btn btn-primary btn-sm" :disabled="replyPosting"
                                            @click="postReply">
                                            {{ replyPosting ? 'Envoi...' : 'Répondre' }}
                                        </button>
                                        <button class="btn btn-ghost btn-sm" @click="closeReply">Annuler</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </article>
    </div>
</template>