<script setup lang="ts">
import { onMounted, Ref, ref } from 'vue';
import OrderItem from './components/OrderItem.vue';
import type { OrderItemType, OrderType } from '@/types';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';

const orderItems: Ref<OrderItemType[]> = ref([]);

const getPendingOrders = () => {
    axios.get('/api/staff/cook/order')
    .then(response => {
        console.log('Pending Orders:', response.data);
        if (response.data.error) {
            console.error('Error fetching pending orders:', response.data.message);
            return;
        }
        orderItems.value = response.data.data;
    }).catch(error => {
        console.error('Error fetching pending orders:', error);
    });
};

onMounted(() => {
    getPendingOrders();
});

const takeOrder = (id: number) => {
    axios.post(`/api/staff/cook/order/${id}/take`)
    .then(response => {
        console.log('Preparing Order:', response.data);
        if (response.data.error) {
            console.error('Error fetching preparing order:', response.data.message);
            return;
        }
        orderItems.value = orderItems.value.filter(item => item.id !== id);
    }).catch(error => {
        console.error('Error fetching preparing order:', error);
    });
};


import { useEcho } from '@laravel/echo-vue';

type OrderEventPayload = {
  order: OrderType
};

type OrdereItemEventPayload = {
  orderItem: OrderItemType
};

onMounted(() => {
    useEcho('cooks.pending-items', '.order.created', (e: OrderEventPayload) => {
        orderItems.value.push(...e.order.items);
    }).listen();

    useEcho('cooks.pending-items', '.order-item.preparing', (e: OrdereItemEventPayload) => {
        orderItems.value = orderItems.value.filter(item => item.id !== e.orderItem.id);
    }).listen();
});

</script>

<template>
    <Head>
        <title>General Orders</title>
    </Head>
    <div>
        <h1>Cooks</h1>
        <a href="/kitchen">назад</a>
        <p>General Orders</p>
        <ul>
            <li>
                <OrderItem
                    v-for="orderItem in orderItems"
                    :key="orderItem.id"
                    :orderItem="orderItem"
                    :takeOrder="takeOrder"
                />
            </li>
        </ul>
    </div>
</template>
