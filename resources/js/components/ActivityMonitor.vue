<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const inactivityTimeout = ref<number | null>(null);
const INACTIVITY_LIMIT = 1 * 60 * 1000; // 5 minut


const url = new URL(window.location.href);
const jwtFromUrl = url.searchParams.get('jwt');

const jwt = jwtFromUrl || localStorage.getItem('jwt');


async function logout() {
    try {
        await axios.post('/logout'); // Wykonaj żądanie POST na endpoint /logout
        window.location.href = '/login'; // Przekierowanie na stronę logowania
    } catch (error) {
        console.error('Błąd podczas wylogowywania:', error);
        window.location.href = '/login'; // Przekierowanie na login w razie błędu
    }
}

function resetInactivityTimer() {
    if (inactivityTimeout.value) {
        clearTimeout(inactivityTimeout.value);
    }

    inactivityTimeout.value = setTimeout(async () => {
        try {
            const token = localStorage.getItem('jwt');
            if (!token) {
                await logout();
                return;
            }

            const response = await axios.get('/api/tokenCheck', {
                headers: {
                    Authorization: `Bearer ${token}`,
                },
            });

            if (response.status !== 200) {
                await logout();
            }
        } catch {
            await logout();
        }
    }, INACTIVITY_LIMIT);
}

onMounted(() => {

    if (jwt) {
        localStorage.setItem('jwt', jwt);
    }
    console.log('JWT:', jwt);

    window.addEventListener('mousemove', resetInactivityTimer);
    window.addEventListener('keydown', resetInactivityTimer);
    resetInactivityTimer();
});

onUnmounted(() => {
    if (inactivityTimeout.value) {
        clearTimeout(inactivityTimeout.value);
    }
    window.removeEventListener('mousemove', resetInactivityTimer);
    window.removeEventListener('keydown', resetInactivityTimer);
});
</script>

<template>
    <div style="display: none;"></div>
</template>
