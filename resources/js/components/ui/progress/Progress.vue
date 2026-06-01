<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(defineProps<{
    value?: number;
    max?: number;
}>(), {
    value: 0,
    max: 100,
});

const percentage = computed(() => {
    return Math.min(100, Math.max(0, (props.value / props.max) * 100));
});
</script>

<template>
    <div
        class="relative h-2 w-full overflow-hidden rounded-full bg-primary/20"
        role="progressbar"
        :aria-valuenow="value"
        :aria-valuemin="0"
        :aria-valuemax="max"
    >
        <div
            class="h-full bg-primary transition-all duration-500 ease-out"
            :style="{ width: `${percentage}%` }"
        />
    </div>
</template>
