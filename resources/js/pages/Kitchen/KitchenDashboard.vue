<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head>
        <title>Kitchen</title>
    </Head>
    <div class="flex flex-col gap-4 m-auto max-w-[400px]">
        <h1>Cooks</h1>
        <p>Kitchen</p>
        <a href="/kitchen/orders" class="m-2 p-2 bg-slate-400 hover:bg-slate-600 rounded"> все заказы</a>
        <a href="/kitchen/personal-orders" class="m-2 p-2 bg-stone-400 hover:bg-stone-600 rounded"> мои заказы</a>
    </div>
</template>
