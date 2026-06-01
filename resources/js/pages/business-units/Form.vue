<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Form, Head, setLayoutProps } from '@inertiajs/vue3';
import { Plus, Trash2 } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { dashboard } from '@/routes';
import { index, store, update } from '@/routes/business-units';

type Category = { id: string; name: string };
type User = { id: string; name: string; role: string };
type Service = { id?: string; name: string };

const props = defineProps<{
    businessUnit: {
        id?: string;
        name?: string;
        description?: string;
        category_id?: string;
        pic?: { id: string; name: string };
        services?: Service[];
        is_active?: boolean;
    } | null;
    categories: Category[];
    users: User[];
}>();

const isEdit = computed(() => !!props.businessUnit?.id);

// Form state
const formData = ref({
    name: props.businessUnit?.name ?? '',
    description: props.businessUnit?.description ?? '',
    category_id: props.businessUnit?.category_id ?? '',
    pic_user_id: props.businessUnit?.pic?.id ?? '',
    is_active: props.businessUnit?.is_active ?? true,
});

// Services management
const services = ref<Service[]>(
    props.businessUnit?.services?.length
        ? [...props.businessUnit.services]
        : [{ name: '' }]
);

function addService() {
    services.value.push({ name: '' });
}

function removeService(index: number) {
    if (services.value.length > 1) {
        services.value.splice(index, 1);
    }
}

// Sync formData when businessUnit prop changes (for edit mode)
watch(
    () => props.businessUnit,
    (unit) => {
        if (unit) {
            formData.value = {
                name: unit.name ?? '',
                description: unit.description ?? '',
                category_id: unit.category_id ?? '',
                pic_user_id: unit.pic?.id ?? '',
                is_active: unit.is_active ?? true,
            };

            if (unit.services?.length) {
                services.value = [...unit.services];
            }
        }
    },
    { immediate: true }
);

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

function handleSubmit() {
    // Services are submitted via form - no need to manually handle
}
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
                    @submit="handleSubmit"
                >
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input
                            id="name"
                            name="name"
                            v-model="formData.name"
                            required
                        />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="category_id">Category</Label>
                        <select
                            id="category_id"
                            name="category_id"
                            v-model="formData.category_id"
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm"
                        >
                            <option value="">Select category</option>
                            <option
                                v-for="cat in categories"
                                :key="cat.id"
                                :value="cat.id"
                            >
                                {{ cat.name }}
                            </option>
                        </select>
                        <InputError :message="errors.category_id" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="pic_user_id">PIC (Person In Charge)</Label>
                        <select
                            id="pic_user_id"
                            name="pic_user_id"
                            v-model="formData.pic_user_id"
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm"
                        >
                            <option value="">Select PIC</option>
                            <option
                                v-for="user in users"
                                :key="user.id"
                                :value="user.id"
                            >
                                {{ user.name }} ({{ user.role }})
                            </option>
                        </select>
                        <InputError :message="errors.pic_user_id" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="description">Description</Label>
                        <textarea
                            id="description"
                            name="description"
                            v-model="formData.description"
                            rows="4"
                            class="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm"
                        />
                        <InputError :message="errors.description" />
                    </div>

                    <!-- Services Management -->
                    <div class="grid gap-2">
                        <Label>Services</Label>
                        <div class="space-y-2">
                            <div
                                v-for="(service, index) in services"
                                :key="index"
                                class="flex items-center gap-2"
                            >
                                <Input
                                    :name="`services[${index}]`"
                                    v-model="service.name"
                                    placeholder="Service name"
                                    class="flex-1"
                                />
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    @click="removeService(index)"
                                    :disabled="services.length <= 1"
                                >
                                    <Trash2 class="size-4" />
                                </Button>
                            </div>
                        </div>
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            @click="addService"
                        >
                            <Plus class="mr-1 size-4" />
                            Add Service
                        </Button>
                        <InputError :message="errors.services" />
                    </div>

                    <div class="flex items-center gap-2">
                        <input
                            type="checkbox"
                            id="is_active"
                            name="is_active"
                            v-model="formData.is_active"
                            value="1"
                            class="size-4 rounded border-input"
                        />
                        <Label for="is_active" class="text-sm font-normal">Active</Label>
                    </div>

                    <Button
                        type="submit"
                        :disabled="processing"
                    >
                        {{ isEdit ? 'Update' : 'Create' }}
                    </Button>
                </Form>
            </CardContent>
        </Card>
    </div>
</template>
