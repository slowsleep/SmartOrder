<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head>
        <title>Service</title>
    </Head>
    <div class="flex flex-col gap-4 m-auto max-w-[400px]">
        <h1>Waiters</h1>
        <p>Service</p>
        <a href="/service/orders" class="m-2 p-2 bg-violet-400 hover:bg-violet-600 rounded"> все заказы</a>
        <a href="/service/personal-orders" class="m-2 p-2 bg-pink-400 hover:bg-pink-600 rounded"> мои заказы</a>
    </div>
</template>
