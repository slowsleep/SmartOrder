<script setup lang="ts">
import { onMounted } from 'vue';
import { Ref, ref } from 'vue';
import type { OrderItemType } from '@/types';
import OrderItem from './components/OrderItem.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { usePage } from '@inertiajs/vue3';

const userId = usePage().props.user_id;

const orderItems: Ref<OrderItemType[]> = ref([]);

const getOwnOrderItems = () => {
    axios.get('/api/staff/cook/order/own')
    .then(response => {
        console.log('Preparing Orders:', response.data);
        if (response.data.error) {
            console.error('Error fetching preparing orders:', response.data.message);
            return;
        }
        orderItems.value = response.data.data;
    }).catch(error => {
        console.error('Error fetching preparing orders:', error);
    })
};

import { useEcho } from '@laravel/echo-vue';

type OrderItemPreparingEventPayload = {
  orderItem: OrderItemType
};

onMounted(() => {
    getOwnOrderItems();
    useEcho(`user.${userId}.preparing-items`, '.order-item.preparing', (e: OrderItemPreparingEventPayload) => {
        orderItems.value.push(e.orderItem);
    }).listen();
});

const readyOrder = (id: number) => {
    axios.post(`/api/staff/cook/order/${id}/ready`)
    .then(response => {
        console.log('Order marked as ready:', response.data);
        orderItems.value = orderItems.value.filter(item => item.id !== id);
    }).catch(error => {
        console.error('Error marking order as ready:', error);
    })
}
</script>

<template>
    <Head>
        <title>Personal Orders</title>
    </Head>
    <div>
        <h1>Cooks</h1>
        <a href="/kitchen">назад</a>
        <p>Personal Orders</p>
        <ul>
            <li>
                <OrderItem
                    v-for="orderItem in orderItems"
                    :key="orderItem.id"
                    :orderItem="orderItem"
                    :readyOrder="readyOrder"
                />
            </li>
        </ul>
    </div>
</template>
