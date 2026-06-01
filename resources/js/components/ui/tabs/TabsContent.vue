<script setup lang="ts">
import { useTabs } from '@/composables/useTabs';

const props = defineProps<{
    value: string;
}>();

const { activeTab } = useTabs();
</script>

<template>
    <div v-if="activeTab === value">
        <slot />
    </div>
</template>
