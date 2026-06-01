<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Bold,
    Italic,
    List,
    ListOrdered,
    Heading2,
    Link,
    Undo,
    Redo,
} from 'lucide-vue-next';

const props = defineProps<{
    modelValue: string;
    placeholder?: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const editorRef = ref<HTMLDivElement | null>(null);
const isReady = ref(false);

function execCommand(command: string, value: string | null = null): void {
    document.execCommand(command, false, value);
    editorRef.value?.focus();
    emit('update:modelValue', editorRef.value?.innerHTML ?? '');
}

function handleInput(): void {
    emit('update:modelValue', editorRef.value?.innerHTML ?? '');
}

function handlePaste(event: ClipboardEvent): void {
    event.preventDefault();
    const text = event.clipboardData?.getData('text/plain') ?? '';
    document.execCommand('insertText', false, text);
}

onMounted(() => {
    if (editorRef.value) {
        editorRef.value.innerHTML = props.modelValue;
        isReady.value = true;
    }
});

watch(() => props.modelValue, (newVal) => {
    if (editorRef.value && editorRef.value.innerHTML !== newVal) {
        editorRef.value.innerHTML = newVal;
    }
});
</script>

<template>
    <div class="border rounded-md overflow-hidden">
        <div class="flex flex-wrap gap-1 p-2 border-b bg-muted/50">
            <Button
                type="button"
                size="sm"
                variant="ghost"
                class="h-8 w-8 p-0"
                title="Bold"
                @click="execCommand('bold')"
            >
                <Bold class="size-4" />
            </Button>
            <Button
                type="button"
                size="sm"
                variant="ghost"
                class="h-8 w-8 p-0"
                title="Italic"
                @click="execCommand('italic')"
            >
                <Italic class="size-4" />
            </Button>
            <div class="w-px h-6 bg-border self-center mx-1" />
            <Button
                type="button"
                size="sm"
                variant="ghost"
                class="h-8 w-8 p-0"
                title="Heading"
                @click="execCommand('formatBlock', '<h3>')"
            >
                <Heading2 class="size-4" />
            </Button>
            <div class="w-px h-6 bg-border self-center mx-1" />
            <Button
                type="button"
                size="sm"
                variant="ghost"
                class="h-8 w-8 p-0"
                title="Bullet List"
                @click="execCommand('insertUnorderedList')"
            >
                <List class="size-4" />
            </Button>
            <Button
                type="button"
                size="sm"
                variant="ghost"
                class="h-8 w-8 p-0"
                title="Numbered List"
                @click="execCommand('insertOrderedList')"
            >
                <ListOrdered class="size-4" />
            </Button>
            <div class="w-px h-6 bg-border self-center mx-1" />
            <Button
                type="button"
                size="sm"
                variant="ghost"
                class="h-8 w-8 p-0"
                title="Undo"
                @click="execCommand('undo')"
            >
                <Undo class="size-4" />
            </Button>
            <Button
                type="button"
                size="sm"
                variant="ghost"
                class="h-8 w-8 p-0"
                title="Redo"
                @click="execCommand('redo')"
            >
                <Redo class="size-4" />
            </Button>
            <Button
                type="button"
                size="sm"
                variant="ghost"
                class="h-8 w-8 p-0"
                title="Insert Link"
                @click="execCommand('createLink', prompt('Enter URL:') || 'https://')"
            >
                <Link class="size-4" />
            </Button>
        </div>
        <div
            ref="editorRef"
            contenteditable="true"
            class="prose prose-sm dark:prose-invert max-w-none p-3 min-h-[100px] focus:outline-none"
            :class="[!modelValue ? 'text-muted-foreground' : '']"
            :data-placeholder="placeholder || 'Start typing...'"
            @input="handleInput"
            @paste="handlePaste"
        />
    </div>
</template>

<style scoped>
[contenteditable="true"]:empty:before {
    content: attr(data-placeholder);
    color: var(--muted-foreground);
    pointer-events: none;
}
</style>
