<script setup>
import { ref, onMounted, computed } from "vue";
import { ChevronDown } from "lucide-vue-next";
import calendar2025 from "../assets/calendar2025.json"


const races = ref([]);
const loading = ref(true);
const error = ref(null);
const now = new Date();

function fmtDate(d) {
  return d.toLocaleDateString("fr-FR", {
    day: "2-digit",
    month: "short",
  });
}
function getFlag(code) {
  const countryCodes = {
    AUS: "au",
    BRN: "bh",
    KSA: "sa",
    AZE: "az",
    MON: "mc",
    ESP: "es",
    CAN: "ca",
    FRA: "fr",
    AUT: "at",
    GBR: "gb",
    HUN: "hu",
    BEL: "be",
    NED: "nl",
    ITA: "it",
    SGP: "sg",
    JPN: "jp",
    USA: "us",
    MEX: "mx",
    BRA: "br",
    QAT: "qa",
    UAE: "ae",
    CHN: "cn",
    PRT: "pt",
    RUS: "ru",
    TUR: "tr",
    ZAF: "za"
  };
  return countryCodes[code] || "us"; // fallback = drapeau US
}

onMounted(async () => {
  loading.value = true;
  try {
    // 🚀 Ici tu utilises directement ton JSON local
races.value = calendar2025.map((m) => {
  const date = new Date(m.date_start.replace("2024", "2025"));
  return {
    id: m.meeting_key,
    name: m.meeting_name,
    country: m.country_name,
    flag: `https://flagcdn.com/w40/${getFlag(m.country_code)}.png`, // 👈 maintenant basé sur country_code
    start: date
  };
});

  } catch (e) {
    console.error("Erreur calendrier", e);
    error.value = "Impossible de charger le calendrier";
  } finally {
    loading.value = false;
  }
});

const upcoming = computed(() =>
  races.value.filter((r) => r.start >= now).sort((a, b) => a.start - b.start)
);

const buttonRace = computed(() => upcoming.value[0] || null);
const dropdownItems = computed(() => upcoming.value.slice(1, 4));
</script>

<template>
  <div class="dropdown dropdown-end z-50">
    <!-- Bouton principal -->
    <label tabindex="0" class="btn btn-ghost gap-2">
      <template v-if="loading">
        <span class="loading loading-spinner loading-sm"></span>
        <span>Calendrier…</span>
      </template>

      <template v-else-if="buttonRace">
        <img
          :src="buttonRace.flag"
          alt="flag"
          class="w-6 h-4 rounded-sm"
        />
        <span>{{ buttonRace.name }} — {{ fmtDate(buttonRace.start) }}</span>
      </template>

      <template v-else>
        <span>Aucune course</span>
      </template>

      <ChevronDown class="w-4 h-4 opacity-70" />
    </label>

    <!-- Dropdown -->
    <ul
      tabindex="0"
      class="dropdown-content p-2 shadow bg-base-100 rounded-box w-96 space-y-2"
    >
      <li v-if="error" class="text-error px-2 py-1">{{ error }}</li>
      <li v-else-if="dropdownItems.length === 0" class="px-2 py-1 opacity-60">
        Pas d'autres courses
      </li>

      <li
        v-for="race in dropdownItems"
        :key="race.id"
        class="flex items-center gap-3 p-2 rounded-lg hover:bg-base-200 transition"
      >
        <!-- Drapeau -->
        <img
          :src="race.flag"
          alt="flag"
          class="w-10 h-6 rounded-sm shadow"
        />

        <!-- Infos course -->
        <div class="flex flex-col">
          <span class="font-semibold text-sm">{{ race.name }}</span>
          <span class="text-xs opacity-70">
            {{ race.country }} — {{ fmtDate(race.start) }}
          </span>
        </div>
      </li>
    </ul>
  </div>
</template>
