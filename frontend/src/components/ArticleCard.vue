<script setup>
const props = defineProps({
  article: { type: Object, required: true },
});

// image en dur (démo)
const DEMO_IMAGE =
  "https://images.unsplash.com/photo-1521412644187-c49fa049e84d?q=80&w=1200&auto=format&fit=crop";

// extrait = premier paragraphe
let firstParagraph = "";
const blocks = props.article.content.blocks;

for (const block of blocks) {
  if (block.type === "paragraph") {
    firstParagraph = block.data.text;
    break;
  }
}

if (firstParagraph.length > 180) {
  firstParagraph = firstParagraph.slice(0, 180) + "...";
}

// date lisible
const date = new Date(props.article.createdAt).toLocaleDateString();

// stats random (démo)
function randomize(min, max) {
  return Math.floor(Math.random() * (max - min + 1)) + min;
}

const likes = randomize(0, 300);
const favs = randomize(0, 120);
const bookmarks = randomize(0, 80);
const comments = randomize(0, 60);
</script>

<template>
  <div class="border-b border-neutral-200 py-6">
    <div class="flex flex-col md:flex-row justify-between gap-6">

      <!-- GAUCHE -->
      <div class="flex-1 flex flex-col justify-between md:w-2/3 max-w-2xl">
        <!-- titre -->
        <h2 class="text-2xl md:text-3xl font-bold leading-tight mb-2">
          {{ article.title }}
        </h2>

        <!-- extrait -->
        <p class="text-sm text-gray-600 mb-4 line-clamp-3">
          {{ firstParagraph }}
        </p>

        <!-- auteur + date -->
        <div class="mt-auto">
          <div class="flex items-center gap-2 text-sm text-gray-700 mb-2">
            <img
              class="w-6 h-6 rounded-full object-cover"
              src="https://placehold.co/40x40"
              alt="avatar"
            />
            <span>{{ article.author.pseudo }}</span>
            <span class="text-gray-400">•</span>
            <span class="text-gray-500">{{ Date }}</span>
          </div>

          <!-- stats random -->
          <div class="flex gap-6 text-sm text-gray-700">
            <span class="flex items-center gap-2">👍 {{ likes }}</span>
            <span class="flex items-center gap-2">⭐ {{ favs }}</span>
            <span class="flex items-center gap-2">🔖 {{ bookmarks }}</span>
            <span class="flex items-center gap-2">💬 {{ comments }}</span>
          </div>
        </div>
      </div>

      <!-- DROITE : image démo -->
      <div class="md:w-[260px] w-full flex justify-end">
        <div class="w-full md:w-[260px] h-[150px] rounded-xl overflow-hidden border border-neutral-200">
          <img :src="DEMO_IMAGE" class="w-full h-full object-cover" alt="cover" />
        </div>
      </div>

    </div>
  </div>
</template>
