<script lang="ts" setup>
import type { OrderItemType } from '@/types';
import { ref, onMounted, onUnmounted, computed } from 'vue';

const props = defineProps({
    orderItem: {
        type: Object as () => OrderItemType,
        required: true
    },
    takeOrder: {
        type: Function || null,
    },
    readyOrder: {
        type: Function || null,
    }
});

const dateCreated = new Date(props.orderItem.created_at);
const currentTime = ref(Date.now());

// Таймер обновления
let timer: ReturnType<typeof setInterval> | null = null;

onMounted(() => {
  timer = setInterval(() => {
    currentTime.value = Date.now()
  }, 1000)
});

onUnmounted(() => {
  if (timer) clearInterval(timer)
});

// Вычисляем разницу в миллисекундах
const timeDiff = computed(() => currentTime.value - dateCreated.getTime());

// Вычисляем минуты
const minutes = computed(() => Math.floor(timeDiff.value / 60000));

const formattedTime = computed(() => {
  return `${minutes.value}`
})
</script>

<template>
    <div class="p-4 border-b border-gray-200 last:border-0 flex flex-row items-center ">
        <div class="flex items-center w-16 font-mono text-sm text-gray-500">
            <p>id:{{ props.orderItem.id }}</p>
        </div>
        <div class="flex items-center justify-between w-full">

            <div>
                <p>{{ props.orderItem.product.name }}</p>
                <p>Время создания: {{ dateCreated.toLocaleTimeString() }}</p>
                <p
                    v-bind:class="{ 'text-green-500': minutes < 15 , 'text-yellow-500': minutes >= 15 && minutes < 30, 'text-red-500': minutes >= 30 }"
                >
                    Минут прошло: {{ formattedTime }}
                </p>
            </div>

            </div>
                <button v-if="props.takeOrder" @click="props.takeOrder(props.orderItem.id)" class="ml-4 px-3 py-1 bg-blue-500 text-white rounded cursor-pointer">
                    Take Order
                </button>

                <button v-if="props.readyOrder" @click="props.readyOrder(props.orderItem.id)" class="ml-4 px-3 py-1 bg-green-500 text-white rounded cursor-pointer">
                    Ready
                </button>
            <div>
        </div>
    </div>
</template>
