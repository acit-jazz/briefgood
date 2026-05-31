import { defineStore } from 'pinia';
import { ref } from 'vue';

export type BriefFilters = {
    search?: string;
    status?: string;
    ai_status?: string;
};

export const useBriefStore = defineStore('brief', () => {
    const filters = ref<BriefFilters>({});

    function setFilters(value: BriefFilters): void {
        filters.value = value;
    }

    function resetFilters(): void {
        filters.value = {};
    }

    return { filters, setFilters, resetFilters };
});
