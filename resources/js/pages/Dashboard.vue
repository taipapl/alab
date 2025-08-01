<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = page.props.user || {};
const results = page.props.results || [];
</script>

<template>

    <Head title="Dashboard" />

    <AppLayout>
        <div class="p-6">
            <!-- Informacje o pacjencie -->
            <div class="mb-6">
                <h2 class="text-xl font-bold">Informacje o pacjencie</h2>
                <p><strong>ID:</strong> {{ user.id }}</p>
                <p><strong>Imię:</strong> {{ user.name }}</p>
                <p><strong>Nazwisko:</strong> {{ user.surname }}</p>
            </div>

            <!-- Tabela wyników -->
            <div>
                <h2 class="text-xl font-bold mb-4">Wyniki badań</h2>
                <table class="table-auto w-full border-collapse border border-gray-300">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border border-gray-300 px-4 py-2">Nazwa testu</th>
                            <th class="border border-gray-300 px-4 py-2">Wartość</th>
                            <th class="border border-gray-300 px-4 py-2">Zakres referencyjny</th>
                            <th class="border border-gray-300 px-4 py-2">Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="result in results" :key="result.created_at">
                            <td class="border border-gray-300 px-4 py-2">{{ result.test_name }}</td>
                            <td class="border border-gray-300 px-4 py-2">{{ result.test_value }}</td>
                            <td class="border border-gray-300 px-4 py-2">{{ result.test_reference }}</td>
                            <td class="border border-gray-300 px-4 py-2">{{ result.created_at }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>