<script setup>
import { Facebook, Twitter, Instagram } from "lucide-vue-next"
</script>

<template>
    <footer class="fixed bottom-0 left-0 right-0 w-full py-4 px-6 text-sm" style="background-color: #282828">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-6">
            <!-- Liens -->
            <div class="flex flex-wrap justify-center gap-4 text-gray-300 text-center">
                <a href="#" class="hover:text-white">Aide</a>
                <a href="#" class="hover:text-white">Contact</a>
                <a href="#" class="hover:text-white">Cookies & Données personnelles</a>
                <a href="#" class="hover:text-white">À propos</a>
                <a href="#" class="hover:text-white">Mentions légales</a>
                <a href="#" class="hover:text-white">Charte de modération</a>
            </div>
            <!-- Droits -->
            <div class="text-xs text-gray-500 text-center md:text-right">
                © 2025 Point de Corde. Tous droits réservés.
            </div>
            <!-- Réseaux sociaux -->
            <div class="flex gap-3">
                <a href="#"
                    class="w-9 h-9 flex items-center justify-center rounded-full border border-white hover:bg-white/10 transition">
                    <Facebook class="w-4 h-4 text-white" />
                </a>
                <a href="#"
                    class="w-9 h-9 flex items-center justify-center rounded-full border border-white hover:bg-white/10 transition">
                    <Twitter class="w-4 h-4 text-white" />
                </a>
                <a href="#"
                    class="w-9 h-9 flex items-center justify-center rounded-full border border-white hover:bg-white/10 transition">
                    <Instagram class="w-4 h-4 text-white" />
                </a>
            </div>
        </div>
    </footer>
</template>
