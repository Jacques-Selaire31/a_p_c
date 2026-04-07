<script setup>


const props = defineProps({
  categories: { type: Array, required: true }, // [{ id, name }]
  modelValue: { type: Array, required: true }, // selectedCategoryIds
  max: { type: Number, default: 3 },
});

const emit = defineEmits(["update:modelValue"]);

function toggle(id) {
    if (props.modelValue.includes(id)) {
        // Supprimer
        emit("update:modelValue", props.modelValue.filter(i => i !== id));
    } else {
        // Ajouter
        if (props.modelValue.length >= props.max) return;
        emit("update:modelValue", [...props.modelValue, id]);
    }
}
</script>

<template>
  <div class="cloud">
    <button
      v-for="c in categories"
      :key="c.id"
      class="category-item"
      :class="{ active: modelValue.includes(c.id) }"
      @click="toggle(c.id)"
    >
      {{ c.name }}
    </button>
  </div>

  <p class="count">Sélection : {{ modelValue.length }} / {{ max }}</p>
</template>

<style scoped>
.cloud {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.category-item {
  border-radius: 9999px;
  padding: 0.4rem 0.9rem;
  font-size: 0.85rem;
  border: 1px solid #e6e6e6;
  background: #fff;
  color: #555;
  cursor: pointer;
  user-select: none;
  transition: 0.12s ease;
}

.category-item.active:hover {
  background: #b91c1c;
  border-color: #b91c1c;
}


.category-item.active {
  background: #dc2626;
  color: #fff;
  border-color: #dc2626;
}


.count {
  margin-top: 0.75rem;
  font-size: 0.85rem;
  color: #6b6b6b;
}
</style>
