<script lang="ts" setup>
import {ref, Ref, onMounted, computed} from 'vue';
import axios from 'axios';
import type { ProductType } from '@/types';
import ProductCard from './components/ProductCard.vue';

const menuItems: Ref<ProductType[]> = ref([]);

const getMenu = () => {
    axios.get('/api/menu')
    .then(response => {
        console.log('Menu:', response.data);
        if (response.data.error) {
            console.error('Error fetching menu:', response.data.message);
            return;
        }
        menuItems.value = response.data.data;
    }).catch(error => {
        console.error('Error fetching menu:', error);
    });
}

const productsInCart: Ref<Record<number, number>> = ref([]);

type CartItemType = {
    product_id: number;
    quantity: number;
}

const getCart = () => {
    axios.get('/api/cart')
    .then(response => {
        console.log('Cart:', response.data);
        if (response.data.error) {
            console.error('Error fetching cart:', response.data.message);
            return;
        }
        console.log('Cart Data:', response.data.data);
        const items = response.data.data.items;
        items.forEach((item: CartItemType) => {
            productsInCart.value[item.product_id] = item.quantity;
        })
    }).catch(error => {
        console.error('Error fetching cart:', error);
    });
}

onMounted(() => {
    getMenu();
    getCart();
});


const addToCart = (productId: number) => {
    console.log('Adding to cart product ID:', productId);
    productsInCart.value[productId] = (productsInCart.value[productId] || 0) + 1;

    axios.post('/api/cart/add', { product_id: productId })
    .then(response => {
        console.log('Add to Cart Response:', response.data);
        if (response.data.error) {
            console.error('Error adding to cart:', response.data.message);
            return;
        }
        console.log('Added to Cart:', response.data.data);
    }).catch(error => {
        console.error('Error adding to cart:', error);
    });
};

const decreaseFromCart = (productId: number) => {
    axios.post('/api/cart/decrease', { product_id: productId })
    .then(response => {
        console.log('Decrease from Cart Response:', response.data);
        if (response.data.error) {
            console.error('Error decreasing from cart:', response.data.message);
            return;
        }
        console.log('Decreased from Cart:', response.data.data);
        productsInCart.value[productId] = (productsInCart.value[productId] || 0) - 1;
    }).catch(error => {
        console.error('Error decreasing from cart:', error);
    });
};

const countInCart = computed(() => {
    return (id: number) => {
        return productsInCart.value[id] || 0;
    };
});

</script>

<template>
    <h1>Menu Page</h1>
    <div class="my-4">
        <a href="/cart" class="m-2 p-2 bg-sky-200 hover:bg-sky-400 text-black rounded">Go to Cart</a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <ProductCard
            v-for="item in menuItems"
            :key="item.id"
            :product="item"
            :addToCart="() => addToCart(item.id)"
            :decreaseFromCart="countInCart(item.id) > 0 ? () => decreaseFromCart(item.id) : undefined"
            :quantity="countInCart(item.id)"
        />
    </div>
</template>
