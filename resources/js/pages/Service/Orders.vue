<script setup lang="ts">
import { onMounted, Ref, ref } from 'vue';
import OrderItem from './components/OrderItem.vue';
import type { OrderItemType } from '@/types';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';

import { useEcho } from '@laravel/echo-vue';

type OrdereItemEventPayload = {
  orderItem: OrderItemType
};

const orderItems: Ref<OrderItemType[]> = ref([]);

const getReadyOrders = () => {
    axios.get('/api/staff/waiter/order')
    .then(response => {
        console.log('Ready Orders:', response.data);
        if (response.data.error) {
            console.error('Error fetching ready orders:', response.data.message);
            return;
        }
        orderItems.value = response.data.data;
    }).catch(error => {
        console.error('Error fetching ready orders:', error);
    });
};

onMounted(() => {
    getReadyOrders();

    useEcho('waiters.ready-items', '.order-item.ready', (e: OrdereItemEventPayload) => {
        orderItems.value.push(e.orderItem);
    }).listen();

    useEcho('waiters.ready-items', '.order-item.in-delivery', (e: OrdereItemEventPayload) => {
        orderItems.value = orderItems.value.filter(item => item.id !== e.orderItem.id);
    }).listen();
});

const takeOrderItem = (id: number) => {
    axios.post(`/api/staff/waiter/order/${id}/take`)
    .then(response => {
        console.log('In delivery Order:', response.data);
        if (response.data.error) {
            console.error('Error fetching in delivery order:', response.data.message);
            return;
        }
        orderItems.value = orderItems.value.filter(item => item.id !== id);
    }).catch(error => {
        console.error('Error fetching in delivery order:', error);
    });
};

</script>

<template>
    <Head>
        <title>General Orders</title>
    </Head>
    <div>
        <h1>Waiters</h1>
        <a href="/service">назад</a>
        <p>General Orders</p>
        <ul>
            <li>
                <OrderItem
                    v-for="orderItem in orderItems"
                    :key="orderItem.id"
                    :orderItem="orderItem"
                    :takeOrder="takeOrderItem"
                />
            </li>
        </ul>
    </div>
</template>
