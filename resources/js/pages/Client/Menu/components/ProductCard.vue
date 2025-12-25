<script lang="ts" setup>
import type { ProductType } from '@/types';

const props = defineProps({
    product: {
        type: Object as () => ProductType,
        required: true
    },
    addToCart: {
        type: Function,
    },
    decreaseFromCart: {
        type: Function
    },
    quantity: {
        type: Number
    }
});

</script>

<template>
    <div class="border border-gray-300 rounded-lg p-4 flex flex-col items-center">
        <h2 class="text-lg font-semibold mb-2">{{ product.name }}</h2>
        <p class="text-gray-600 mb-4">{{ product.description }}</p>
        <div class="text-xl font-bold mb-4">{{ product.price }} руб.</div>

        <div class="flex space-x-2">
            <button v-if="props.addToCart" @click="props.addToCart(product.id)" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 cursor-pointer">+</button>
            <button v-if="props.decreaseFromCart" @click="props.decreaseFromCart(product.id)" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 cursor-pointer">-</button>
            <p v-if="props.quantity">{{ props.quantity }}</p>
        </div>
    </div>
</template>
