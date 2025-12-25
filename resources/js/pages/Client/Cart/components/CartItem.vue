<script lang="ts" setup>
import type { ProductType } from '@/types';

const props = defineProps({
    product: {
        type: Object as () => ProductType,
        required: true
    },
    quantity: {
        type: Number,
        required: true
    },
    addToCart: {
        type: Function,
    },
    decreaseFromCart: {
        type: Function
    },
    removeFromCart: {
        type: Function,
    }
});

import { onMounted } from 'vue';

onMounted(() => {
    console.log('CartProduct Mounted with product:', props.product, 'and quantity:', props.quantity);
});

</script>

<template>
    <div>
        <h2 class="text-lg font-semibold mb-2">{{ props.product.name }}</h2>
        <div class="text-xl font-bold mb-4">{{ props.product.price }} руб.</div>
        <div class="text-md font-medium mb-4">Кол-во: {{ props.quantity }}</div>

        <button v-if="props.addToCart" @click="props.addToCart(product)" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-blue-600">+</button>
        <button v-if="props.decreaseFromCart" @click="props.decreaseFromCart(product)" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">-</button>
        <button v-if="props.removeFromCart" @click="props.removeFromCart(product)" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-blue-600">x</button>
    </div>
</template>