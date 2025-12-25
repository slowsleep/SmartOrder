<script lang="ts" setup>
import CartItem from './components/CartItem.vue';
import {ref, Ref, onMounted} from 'vue';
import axios from 'axios';
import type { ProductType } from '@/types';

const cartItems: Ref<ProductType[]> = ref([]);

const isNotOrderCreated = ref(true);

const getCart = () => {
    axios.get('/api/cart')
    .then(response => {
        console.log('Cart:', response.data);
        if (response.data.error) {
            console.error('Error fetching cart:', response.data.message);
            return;
        }
        console.log('Cart Data:', response.data.data);
        cartItems.value = response.data.data.items;
    }).catch(error => {
        console.error('Error fetching cart:', error);
    });
}

onMounted(() => {
    getCart();
});

type OrderPaymentPayloadType = {
    guest_token: string;
    order_id: number;
};

const createdOrderPayload = ref<OrderPaymentPayloadType | null>(null);

const makeOrder = () => {
    axios.post('/api/order')
    .then(response => {
        console.log('Order Response:', response.data);
        if (response.data.error) {
            console.error('Error making order:', response.data.message);
            return;
        }
        console.log('Order Successful:', response.data.data);
        createdOrderPayload.value = response.data.data;
        isNotOrderCreated.value = false;
    }).catch(error => {
        console.error('Error making order:', error);
    });
};

const payOrder = () => {
    if (!createdOrderPayload.value) {
        alert('No order to pay for. Please make an order first.');
        return;
    }

    axios.post(`/api/order/${createdOrderPayload.value.order_id}/pay`)
    .then(response => {
        console.log('Payment Response:', response.data);

        if (response.data.error) {
            console.error('Error paying for order:', response.data.message);
            return;
        }

        console.log('Payment Successful:', response.data.data);
        console.log('Redirecting to order page...');

        if (createdOrderPayload.value) {
            window.location.href = "/order/" + createdOrderPayload.value.order_id;
        }
    }).catch(error => {
        console.error('Error paying for order:', error);
        alert(error.response.data.message);
    });
};

const clearCart = () => {
    axios.post('/api/cart/clear')
    .then(response => {
        console.log('Clear Cart Response:', response.data);
        if (response.data.error) {
            console.error('Error clearing cart:', response.data.message);
            return;
        }
        console.log('Cart Cleared:', response.data.data);
        cartItems.value = [];
    }).catch(error => {
        console.error('Error clearing cart:', error);
    });
}
</script>

<template>
    <h1>Cart Page</h1>
    <div class="my-4">
        <a href="/menu" class="m-2 p-2 bg-purple-200 hover:bg-purple-400 text-black rounded">Back to Menu</a>
    </div>
    <div>
        <div v-if="cartItems && cartItems.length > 0" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <CartItem
                v-for="item in cartItems"
                :key="item.id"
                :product="item"
                :quantity="item.quantity"
            />
        </div>

        <p v-if="cartItems.length === 0">Корзина пуста</p>

        <button @click="makeOrder" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg cursor-pointer">Сделать заказ</button>
        <!-- TODO: потом добавить всплывающее окно с фейковой оплатой -->
        <button
            @click="payOrder"
            :class="`text-white font-bold py-2 px-4 rounded-lg ${isNotOrderCreated ? 'bg-gray-500 cursor-not-allowed' : 'bg-green-500 hover:bg-green-700 cursor-pointer'}`"
            :disabled="isNotOrderCreated"
        >Оплатить заказ</button>
        <button @click="clearCart" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg cursor-pointer">Очистить корзину</button>
    </div>
</template>
