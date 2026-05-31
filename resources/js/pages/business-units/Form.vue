<script setup lang="ts">
import { Form, Head, setLayoutProps } from '@inertiajs/vue3';
import { computed, watchEffect } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index, store, update } from '@/routes/business-units';
import { dashboard } from '@/routes';

type Category = { id: string; name: string };

const props = defineProps<{
    businessUnit: {
        id?: string;
        name?: string;
        description?: string;
        category_id?: string;
    } | null;
    categories: Category[];
}>();

const isEdit = computed(() => !!props.businessUnit?.id);

watchEffect(() => {
    setLayoutProps({
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Business Units', href: index() },
            {
                title: isEdit.value ? 'Edit' : 'Create',
                href: '#',
            },
        ],
    });
});
</script>

<template>
    <Head :title="isEdit ? 'Edit Business Unit' : 'Create Business Unit'" />

    <div class="mx-auto max-w-xl p-4">
        <Card>
            <CardHeader>
                <CardTitle>{{ isEdit ? 'Edit' : 'Create' }} Business Unit</CardTitle>
            </CardHeader>
            <CardContent>
                <Form
                    :action="isEdit ? update(businessUnit!.id!).url : store().url"
                    :method="isEdit ? 'put' : 'post'"
                    v-slot="{ errors, processing }"
                    class="grid gap-4"
                >
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input
                            id="name"
                            name="name"
                            :default-value="businessUnit?.name"
                            required
                        />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="category_id">Category</Label>
                        <select
                            id="category_id"
                            name="category_id"
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm"
                        >
                            <option value="">Select category</option>
                            <option
                                v-for="cat in categories"
                                :key="cat.id"
                                :value="cat.id"
                                :selected="cat.id === businessUnit?.category_id"
                            >
                                {{ cat.name }}
                            </option>
                        </select>
                        <InputError :message="errors.category_id" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="description">Description</Label>
                        <textarea
                            id="description"
                            name="description"
                            rows="4"
                            class="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm"
                            :default-value="businessUnit?.description"
                        />
                        <InputError :message="errors.description" />
                    </div>

                    <Button
                        type="submit"
                        :disabled="processing"
                    >
                        Save
                    </Button>
                </Form>
            </CardContent>
        </Card>
    </div>
</template>
