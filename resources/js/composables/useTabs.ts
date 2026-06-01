import { ref } from 'vue';

const activeTab = ref('scope');

export function useTabs() {
    return {
        activeTab,
        setTab: (tab: string) => {
            activeTab.value = tab;
        },
    };
}
