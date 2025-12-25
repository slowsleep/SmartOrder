<script setup lang="ts">
import { onMounted } from 'vue';
import { Ref, ref } from 'vue';
import type { OrderItemType } from '@/types';
import OrderItem from './components/OrderItem.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { usePage } from '@inertiajs/vue3';
import { useEcho } from '@laravel/echo-vue';

const userId = usePage().props.user_id;

const orderItems: Ref<OrderItemType[]> = ref([]);

const getOwnOrderItemss = () => {
    axios.get('/api/staff/waiter/order/own')
    .then(response => {
        console.log('In delivery Orders:', response.data);
        if (response.data.error) {
            console.error('Error fetching in delivery orders:', response.data.message);
            return;
        }
        orderItems.value = response.data.data;
    }).catch(error => {
        console.error('Error fetching in delivery orders:', error);
    })
};

type OrderItemInDeliveryEventPayload = {
  orderItem: OrderItemType
};

onMounted(() => {
    getOwnOrderItemss();
    useEcho(`user.${userId}.delivery-items`, '.order-item.in-delivery', (e: OrderItemInDeliveryEventPayload) => {
        orderItems.value.push(e.orderItem);
    }).listen();
});

const servedOrder = (id: number) => {
    axios.post(`/api/staff/waiter/order/${id}/served`)
    .then(response => {
        console.log('Order marked as served:', response.data);
        orderItems.value = orderItems.value.filter(item => item.id !== id);
    }).catch(error => {
        console.error('Error marking order as served:', error);
    })
}
</script>

<template>
    <Head>
        <title>Personal Orders</title>
    </Head>
    <div>
        <h1>Waiters</h1>
        <a href="/service">назад</a>
        <p>Personal Orders</p>
        <ul>
            <li>
                <OrderItem
                    v-for="orderItem in orderItems"
                    :key="orderItem.id"
                    :orderItem="orderItem"
                    :servedOrder="servedOrder"
                />
            </li>
        </ul>
    </div>
</template>
