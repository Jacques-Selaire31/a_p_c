<script setup>
import { onMounted, onBeforeUnmount, ref } from "vue";
import EditorJS from "@editorjs/editorjs";
import Paragraph from "@editorjs/paragraph";
import Header from "@editorjs/header";
import List from "@editorjs/list";
import Quote from "@editorjs/quote";
import Checklist from "@editorjs/checklist";
import Delimiter from "@editorjs/delimiter";
import LinkTool from "@editorjs/link";
import ImageTool from "@editorjs/image";
import Embed from "@editorjs/embed";
import Table from "@editorjs/table";
import Marker from "@editorjs/marker";
import CategoryCloud from "../components/CategoryCloud.vue";

const API = import.meta.env.VITE_API_URL;

const title = ref("");
const loading = ref(false);
const message = ref("");

const categories = ref([]); // [{ id, name }]
const selectedCategoryIds = ref([]); // [1,2,3]

let editor = null;

onMounted(async () => {
    editor = new EditorJS({
        holder: "editor",
        placeholder: "Ecris ton article ici...",
        inlineToolbar: ["bold", "italic", "link"],

        tools: {
            header: {
                class: Header,
                inlineToolbar: true,
                config: {
                    placeholder: "Titre de section…",
                    levels: [2, 3, 4, 5, 6],
                    defaultLevel: 2,
                },
            },

            paragraph: {
                class: Paragraph,
                inlineToolbar: true,
            },

            list: {
                class: List,
                inlineToolbar: true,
            },

            quote: {
                class: Quote,
                inlineToolbar: true,
                config: {
                    quotePlaceholder: "Citation…",
                    captionPlaceholder: "Auteur (optionnel)",
                },
            },

            checklist: {
                class: Checklist,
                inlineToolbar: true,
            },
            delimiter: Delimiter,

            linkTool: {
                class: LinkTool,
                config: {
                    endpoint: `${API}/api/link/metadata`,
                },
            },
            image: {
                class: ImageTool,
                config: {
                    uploader: {
                        uploadByUrl(url) {
                            return Promise.resolve({
                                success: 1,
                                file: { url }
                            });
                        }
                    }
                }
            },
            embed: {
                class: Embed,
                config: {
                    services: {
                        youtube: true,
                        twitter: true,
                        instagram: true,
                    }
                }
            },
            table: {
                class: Table,
                inlineToolbar: true,
            },
            marker: {
                class: Marker,
            },
        },
    });

    await getCategories();
});

onBeforeUnmount(() => {
    editor?.destroy?.();
    editor = null;
});

async function getCategories() {
    try {
        const response = await fetch(`${API}/api/public/categories`, {
            headers: { Accept: "application/json" },
        });

        if (!response.ok) throw new Error(await response.text());
        const data = await response.json();
        //const categories = ref([]);  [{ id, name }]
        categories.value = data.map((c) => ({
            id: c.id,
            name: c.name,
        }));
    } catch (e) {
        console.error(e);
        message.value = "Impossible de charger les catégories";
    }
}

async function save() {

    const token = localStorage.getItem("token");

    try {
        const content = await editor.save();
        const req = await fetch(`${API}/api/article`, {
            method: "POST",
            headers: {
                Accept: "application/json",
                "Content-Type": "application/json",
                Authorization: `Bearer ${token}`,
            },
            body: JSON.stringify({
                title: title.value,
                content, // bloc Editor.js
                categories: selectedCategoryIds.value,
            }),
        });

        if (!req.ok) throw new Error(await req.text());

        const created = await req.json();
        message.value = `Article créé`;

        // reset
        title.value = "";
        selectedCategoryIds.value = [];
        await editor.render({ blocks: [] });
    } catch (e) {
        console.error(e);
        message.value = "Erreur création article";
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div class="editor-page">
        <div class="editor-container">
            <!-- Titre -->
            <input v-model="title" type="text" class="editor-title" placeholder="Titre" />
            <!-- Aide catégories -->
            <p class="editor-advice">
                Veuillez sélectionner les catégories de l'article dans le nuage de mots</p>


            <CategoryCloud v-model="selectedCategoryIds" :categories="categories" :max="3" />

            <!-- Editor -->
            <div id="editor" class="editor-content"></div>


            <!-- Bouton -->
            <div class="editor-actions">
                <button class="btn btn-neutral" :disabled="loading ||
                    selectedCategoryIds.length === 0 ||
                    selectedCategoryIds.length > 3" @click="save">
                    {{ loading ? "Envoi..." : "Publier" }}
                </button>
            </div>
            <p v-if="message" class="editor-message">{{ message }}</p>
        </div>
    </div>
</template>

<style scoped>
.editor-page {
    display: flex;
    justify-content: center;
    padding: 4rem 1rem;
    background: #fff;
}

.editor-container {
    width: 100%;
    max-width: 740px;
}

.editor-title {
    width: 100%;
    border: none;
    outline: none;
    font-size: 3rem;
    font-weight: 800;
    line-height: 1.15;
    color: #111;
    margin: 0 0 0.75rem 0;
}

.editor-title::placeholder {
    color: #c2c2c2;
}

.editor-advice {
    margin: 0 0 1.25rem 0;
    color: #6b6b6b;
    font-size: 0.95rem;
}

.editor-content {
    margin-top: 1.75rem;
    padding: 1.5rem 1.25rem;
    font-size: 1.15rem;
    line-height: 1.75;
    color: #242424;

    border: 1px solid #e6e6e6;
    border-radius: 6px;
}


:deep(.ce-paragraph) {
    margin: 0 0 1.2rem 0;
}

:deep(.ce-header) {
    font-weight: 800;
    margin: 2.25rem 0 0.75rem 0;
    line-height: 1.25;
}

:deep(.ce-header[data-level="2"]) {
    font-size: 1.9rem;
}

:deep(.ce-header[data-level="3"]) {
    font-size: 1.5rem;
}

:deep(.ce-toolbar__plus) {
    color: #bdbdbd;
}

:deep(.ce-toolbar__plus:hover) {
    color: #111;
}

:deep(.ce-toolbar__settings-btn) {
    color: #bdbdbd;
}

:deep(.ce-toolbar__settings-btn:hover) {
    color: #111;
}

/* Actions */
.editor-actions {
    margin-top: 3rem;
    display: flex;
    justify-content: flex-end;
}

.editor-message {
    margin-top: 1rem;
    color: #444;
}
</style>
