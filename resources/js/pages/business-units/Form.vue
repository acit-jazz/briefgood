<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Form, Head, setLayoutProps } from '@inertiajs/vue3';
import { Trash2 } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { dashboard } from '@/routes';
import { index, store, update } from '@/routes/business-units';

type Category = { id: string; name: string };
type User = { id: string; name: string; role: string };
type AvailableService = { id: string; name: string; keywords?: string[] };
type AttachedService = {
    id?: string;
    service_id: string;
    specialization_score: number;
    notes: string;
};

const props = defineProps<{
    businessUnit: {
        id?: string;
        name?: string;
        description?: string;
        category_id?: string;
        pic?: { id: string; name: string };
        services?: Array<{
            id: string;
            name: string;
            specialization_score: number;
            notes?: string;
        }>;
        is_active?: boolean;
    } | null;
    categories: Category[];
    users: User[];
    services: AvailableService[];
}>();

const isEdit = computed(() => !!props.businessUnit?.id);

// Form state
console.log('Initial business unit prop:', props.businessUnit);
const formData = ref({
    name: props.businessUnit?.name ?? '',
    description: props.businessUnit?.description ?? '',
    category_id: props.businessUnit?.category_id ?? '',
    pic_user_id: props.businessUnit?.pic?.id ?? '',
    is_active: props.businessUnit?.is_active ?? true,
});

// Attached services with specialization scores
const attachedServices = ref<AttachedService[]>([]);

// Get available services that are not yet attached
const availableServices = computed(() => {
    const attachedIds = attachedServices.value.map(s => s.service_id);
    return props.services.filter(s => !attachedIds.includes(s.id));
});

// Initialize attached services from props
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
                attachedServices.value = unit.services.map(s => ({
                    service_id: s.id,
                    specialization_score: s.specialization_score ?? 50,
                    notes: s.notes ?? '',
                }));
            }
        }
    },
    { immediate: true }
);

function addService() {
    // Don't add if no available services
    if (availableServices.value.length === 0) {
        return;
    }

    const firstAvailable = availableServices.value[0];
    attachedServices.value.push({
        service_id: firstAvailable.id,
        specialization_score: 50,
        notes: '',
    });
}

function removeService(index: number) {
    attachedServices.value.splice(index, 1);
}

function getServiceName(serviceId: string): string {
    return props.services.find(s => s.id === serviceId)?.name ?? 'Unknown';
}

function getScoreColor(score: number): string {
    if (score >= 80) {
        return 'text-green-600';
    }
    if (score >= 60) {
        return 'text-yellow-600';
    }
    return 'text-red-600';
}


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

    <div class="mx-auto w-full p-4">
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
                        <Label for="category_id">Category </Label>
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

                    <!-- Services Management with Specialization Scores -->
                    <div class="grid gap-2">
                        <Label>Services & Specialization</Label>
                        <p class="text-xs text-muted-foreground">
                            Add services this business unit can provide, with a specialization score (1-100) indicating expertise level.
                        </p>

                        <div class="space-y-3">
                            <div
                                v-for="(service, index) in attachedServices"
                                :key="index"
                                class="rounded-lg border p-3 space-y-2"
                            >
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-sm">{{ getServiceName(service.service_id) }}</span>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        @click="removeService(index)"
                                    >
                                        <Trash2 class="size-4 text-destructive" />
                                    </Button>
                                </div>

                                <!-- Hidden input to submit service_id -->
                                <input
                                    type="hidden"
                                    :name="`services[${index}][service_id]`"
                                    :value="service.service_id"
                                />

                                <div class="grid grid-cols-2 gap-2">
                                    <div class="space-y-1">
                                        <Label class="text-xs">Specialization Score</Label>
                                        <div class="flex items-center gap-2">
                                            <input
                                                type="range"
                                                min="1"
                                                max="100"
                                                v-model.number="service.specialization_score"
                                                class="flex-1"
                                            />
                                            <span
                                                class="text-sm font-medium w-8"
                                                :class="getScoreColor(service.specialization_score)"
                                            >
                                                {{ service.specialization_score }}
                                            </span>
                                        </div>
                                        <input
                                            type="hidden"
                                            :name="`services[${index}][specialization_score]`"
                                            :value="service.specialization_score"
                                        />
                                    </div>

                                    <div class="space-y-1">
                                        <Label class="text-xs">Notes (optional)</Label>
                                        <Input
                                            :name="`services[${index}][notes]`"
                                            v-model="service.notes"
                                            placeholder="e.g. 5+ years experience"
                                            class="h-7 text-xs"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-2 items-center" v-if="availableServices.length > 0">
                            <select
                                @change="(e) => { const val = (e.target as HTMLSelectElement).value; if(val) { attachedServices.push({ service_id: val, specialization_score: 50, notes: '' }); (e.target as HTMLSelectElement).value = ''; } }"
                                class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm"
                            >
                                <option value="">Select a service to add...</option>
                                <option
                                    v-for="service in availableServices"
                                    :key="service.id"
                                    :value="service.id"
                                >
                                    {{ service.name }}
                                </option>
                            </select>
                        </div>

                        <p v-else-if="services.length === 0" class="text-xs text-muted-foreground">
                            No services available. Please create services first.
                        </p>
                        <p v-else class="text-xs text-muted-foreground">
                            All services have been added.
                        </p>

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
