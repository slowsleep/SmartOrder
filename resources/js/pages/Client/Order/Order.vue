<script lang="ts" setup>

import { useEchoPublic } from '@laravel/echo-vue';
import { onMounted, ref } from 'vue';
import axios from 'axios';
import type { OrderType } from '@/types';

type OrderStatusUpdatedEventPayload = {
    orderId: string;
    orderStatus: string;
}

type OrderItemStatusUpdatedEventPayload = {
    orderItemId: string;
    orderItemStatus: string;
}

const orderStatus = ref<string | null>(null);

const orderItemsStatus = ref<Record<string, string>>({});

const order = ref<OrderType | null>(null);
const orderId = ref<string | null>(null);

const getOrder = () => {
    axios.get(`/api/order/${orderId.value}`)
    .then(response => {
        console.log('Order Status:', response.data);
        if (response.data.error) {
            console.error('Error fetching order status:', response.data.message);
            return;
        }
        order.value = response.data.data;
    }).catch(error => {
        console.error('Error fetching order status:', error);
    })
}

onMounted(() => {
    const url = window.location.href;
    const parts = url.split('/');
    orderId.value = parts[parts.length - 1].split('?')[0];

    getOrder();

    useEchoPublic('order.' + orderId.value, '.order.status-updated', (e: OrderStatusUpdatedEventPayload) => {
        console.log('Order Status Updated:', e);
        orderStatus.value= e.orderStatus;
    }).listen();

    useEchoPublic('order.' + orderId.value, '.order-item.status-updated', (e: OrderItemStatusUpdatedEventPayload) => {
        console.log('Order Item Status Updated:', e);
        orderItemsStatus.value[e.orderItemId] = e.orderItemStatus;
    }).listen();
});
</script>

<template>
    <h1>Order Status</h1>
    {{ orderStatus ?? 'No status' }}
    <div v-if="order">
        <p>№{{ order.id }}</p>
        <p>Статус: {{ orderStatus ?? order.status }}</p>
        <p>Блюда: </p>
        <ul>
            <li v-for="item in order.items" v-bind:key="item.id">
                <div>
                    <p>{{ item.product.name }}</p>
                    <p>Статус: {{ orderItemsStatus[item.id] ?? item.status }}</p>
                </div>
            </li>
        </ul>
    </div>
</template>
