<script setup lang="ts">
import { useTabs } from '@/composables/useTabs';

const props = defineProps<{
    value: string;
}>();

const { activeTab, setTab } = useTabs();
</script>

<template>
    <button
        type="button"
        :class="[
            'px-4 py-2 text-sm font-medium border-b-2 transition-colors',
            activeTab === value
                ? 'text-foreground border-primary'
                : 'text-muted-foreground border-transparent hover:text-foreground',
        ]"
        @click="setTab(value)"
    >
        <slot />
    </button>
</template>
