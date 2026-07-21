<script setup lang="ts">
import { Head, setLayoutProps, router } from '@inertiajs/vue3';
import { Trash2, Upload, X, Plus } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
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
    service_name?: string; // For newly created services
    specialization_score: number;
    notes: string;
};

const props = defineProps<{
    businessUnit: {
        id?: string;
        name?: string;
        description?: string;
        logo_path?: string;
        logo_url?: string;
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
    errors?: Record<string, string>;
}>();

const isEdit = computed(() => !!props.businessUnit?.id);

// Form state
const form = ref({
    name: props.businessUnit?.name ?? '',
    description: props.businessUnit?.description ?? '',
    category_id: props.businessUnit?.category_id ?? '',
    pic_user_id: props.businessUnit?.pic?.id ?? '',
    is_active: props.businessUnit?.is_active ?? true,
});

const logoFile = ref<File | null>(null);
const removeLogo = ref(false);
const logoPreview = ref<string | null>(props.businessUnit?.logo_url ?? null);
const processing = ref(false);

// Attached services with specialization scores
const attachedServices = ref<AttachedService[]>([]);

// Service dialog state
const showServiceDialog = ref(false);
const selectedServiceId = ref('');
const newServiceName = ref('');
const isCreatingNewService = ref(false);

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
            form.value = {
                name: unit.name ?? '',
                description: unit.description ?? '',
                category_id: unit.category_id ?? '',
                pic_user_id: unit.pic?.id ?? '',
                is_active: unit.is_active ?? true,
            };
            logoPreview.value = unit.logo_url ?? null;

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

function handleLogoChange(event: Event) {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (file) {
        logoFile.value = file;
        removeLogo.value = false;
        // Create preview URL
        const reader = new FileReader();
        reader.onload = (e) => {
            logoPreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
}

function removeLogoHandler() {
    logoFile.value = null;
    removeLogo.value = true;
    logoPreview.value = null;
}

function openServiceDialog() {
    selectedServiceId.value = '';
    newServiceName.value = '';
    isCreatingNewService.value = false;
    showServiceDialog.value = true;
}

function closeServiceDialog() {
    showServiceDialog.value = false;
    selectedServiceId.value = '';
    newServiceName.value = '';
    isCreatingNewService.value = false;
}

async function confirmAddService() {
    if (isCreatingNewService.value && newServiceName.value.trim()) {
        // Create new service via API first
        try {
            const response = await fetch('/services', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    name: newServiceName.value.trim(),
                    is_active: true,
                }),
            });

            const data = await response.json();

            if (!response.ok || !data.id) {
                throw new Error(data.message || 'Failed to create service');
            }

            attachedServices.value.push({
                service_id: data.id,
                service_name: data.name || newServiceName.value.trim(),
                specialization_score: 50,
                notes: '',
            });
            closeServiceDialog();
        } catch (error) {
            console.error(error);
        }
    } else if (selectedServiceId.value) {
        attachedServices.value.push({
            service_id: selectedServiceId.value,
            specialization_score: 50,
            notes: '',
        });
        closeServiceDialog();
    }
}

function slugify(text: string): string {
    return text.toLowerCase().replace(/\s+/g, '-');
}

function removeService(index: number) {
    attachedServices.value.splice(index, 1);
}

function getServiceName(serviceId: string, attachedService?: AttachedService): string {
    if (attachedService?.service_name) {
        return attachedService.service_name;
    }

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
    processing.value = true;

    // Build FormData for proper file upload
    const formData = new FormData();
    formData.append('name', form.value.name);
    formData.append('description', form.value.description);
    formData.append('category_id', form.value.category_id);
    formData.append('pic_user_id', form.value.pic_user_id);
    formData.append('is_active', form.value.is_active ? '1' : '0');
    formData.append('_method', isEdit.value ? 'put' : 'post');

    if (logoFile.value) {
        formData.append('logo', logoFile.value);
    }

    if (removeLogo.value) {
        formData.append('remove_logo', '1');
    }

    // Append services
    attachedServices.value.forEach((service, index) => {
        formData.append(`services[${index}][service_id]`, service.service_id);
        formData.append(`services[${index}][specialization_score]`, service.specialization_score.toString());

        if (service.notes) {
            formData.append(`services[${index}][notes]`, service.notes);
        }
    });

    const url = isEdit.value
        ? update(props.businessUnit!.id!).url
        : store().url;

    router.post(url, formData, {
        onFinish: () => {
            processing.value = false;
        },
    });
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
                <form @submit.prevent="handleSubmit" class="grid gap-4">
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input
                            id="name"
                            name="name"
                            v-model="form.name"
                            required
                        />
                        <InputError :message="props.errors?.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="category_id">Category </Label>
                        <select
                            id="category_id"
                            name="category_id"
                            v-model="form.category_id"
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
                        <InputError :message="props.errors?.category_id" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="pic_user_id">PIC (Person In Charge)</Label>
                        <select
                            id="pic_user_id"
                            name="pic_user_id"
                            v-model="form.pic_user_id"
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
                        <InputError :message="props.errors?.pic_user_id" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="description">Description</Label>
                        <textarea
                            id="description"
                            name="description"
                            v-model="form.description"
                            rows="4"
                            class="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm"
                        />
                        <InputError :message="props.errors?.description" />
                    </div>

                    <!-- Logo Upload -->
                    <div class="grid gap-2">
                        <Label>Logo</Label>

                        <!-- Current Logo Preview -->
                        <div v-if="logoPreview && !removeLogo" class="relative inline-block">
                            <img
                                :src="logoPreview"
                                alt="Current logo"
                                class="h-24 w-24 rounded-lg border object-contain"
                            />
                            <button
                                type="button"
                                @click="removeLogoHandler"
                                class="absolute -right-2 -top-2 rounded-full bg-destructive p-1 text-destructive-foreground hover:bg-destructive/80"
                            >
                                <X class="size-3" />
                            </button>
                        </div>

                        <!-- Upload New Logo -->
                        <div v-if="!logoPreview || removeLogo">
                            <label
                                for="logo"
                                class="flex h-32 w-full cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-muted-foreground/25 bg-transparent hover:border-muted-foreground/50"
                            >
                                <Upload class="mb-2 size-8 text-muted-foreground" />
                                <span class="text-sm text-muted-foreground">
                                    Click to upload logo
                                </span>
                                <span class="text-xs text-muted-foreground">
                                    JPG, PNG, SVG, or WebP (max 2MB)
                                </span>
                            </label>
                            <input
                                id="logo"
                                name="logo"
                                type="file"
                                accept="image/jpeg,image/png,image/svg+xml,image/webp"
                                class="hidden"
                                @change="handleLogoChange"
                            />
                        </div>

                        <!-- Replace logo when current exists -->
                        <div v-if="logoPreview && !removeLogo" class="mt-2">
                            <label
                                for="logo_replace"
                                class="flex h-20 w-full cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-muted-foreground/25 bg-transparent hover:border-muted-foreground/50"
                            >
                                <Upload class="mb-1 size-5 text-muted-foreground" />
                                <span class="text-xs text-muted-foreground">
                                    Click to replace logo
                                </span>
                            </label>
                            <input
                                id="logo_replace"
                                name="logo"
                                type="file"
                                accept="image/jpeg,image/png,image/svg+xml,image/webp"
                                class="hidden"
                                @change="handleLogoChange"
                            />
                        </div>

                        <!-- Hidden input to submit logo removal -->
                        <input
                            v-if="removeLogo"
                            type="hidden"
                            name="remove_logo"
                            value="1"
                        />

                        <InputError :message="props.errors?.logo" />
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
                                    <span class="font-medium text-sm">{{ getServiceName(service.service_id, service) }}</span>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        @click="removeService(index)"
                                    >
                                        <Trash2 class="size-4 text-destructive" />
                                    </Button>
                                </div>

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
                                    </div>

                                    <div class="space-y-1">
                                        <Label class="text-xs">Notes (optional)</Label>
                                        <Input
                                            v-model="service.notes"
                                            placeholder="e.g. 5+ years experience"
                                            class="h-7 text-xs"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <Button
                            v-if="availableServices.length > 0"
                            type="button"
                            variant="outline"
                            size="sm"
                            @click="openServiceDialog"
                        >
                            <Plus class="mr-1 size-4" />
                            Add Service
                        </Button>

                        <p v-else-if="services.length === 0" class="text-xs text-muted-foreground">
                            No services available. Please create services first.
                        </p>
                        <p v-else class="text-xs text-muted-foreground">
                            All services have been added.
                        </p>

                        <InputError :message="props.errors?.services" />
                    </div>

                    <div class="flex items-center gap-2">
                        <input
                            type="checkbox"
                            id="is_active"
                            name="is_active"
                            v-model="form.is_active"
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
                </form>
            </CardContent>
        </Card>
    </div>

    <!-- Service Selection Dialog -->
    <Dialog v-model:open="showServiceDialog">
        <DialogContent class="max-w-md">
            <DialogHeader>
                <DialogTitle>Add Service</DialogTitle>
                <DialogDescription>Select an existing service or create a new one.</DialogDescription>
            </DialogHeader>

            <div class="space-y-4 py-4">
                <div class="flex gap-2">
                    <button
                        type="button"
                        class="flex-1 rounded-lg border p-3 text-center text-sm transition-colors"
                        :class="!isCreatingNewService ? 'border-primary bg-primary/10 text-primary' : 'border-border hover:bg-muted'"
                        @click="isCreatingNewService = false"
                    >
                        Select Existing
                    </button>
                    <button
                        type="button"
                        class="flex-1 rounded-lg border p-3 text-center text-sm transition-colors"
                        :class="isCreatingNewService ? 'border-primary bg-primary/10 text-primary' : 'border-border hover:bg-muted'"
                        @click="isCreatingNewService = true"
                    >
                        Create New
                    </button>
                </div>

                <div v-if="!isCreatingNewService">
                    <label class="text-sm font-medium mb-2 block">Select Service</label>
                    <select
                        v-model="selectedServiceId"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                    >
                        <option value="">Choose a service...</option>
                        <option
                            v-for="service in availableServices"
                            :key="service.id"
                            :value="service.id"
                        >
                            {{ service.name }}
                        </option>
                    </select>
                </div>

                <div v-else>
                    <label class="text-sm font-medium mb-2 block">Service Name</label>
                    <Input
                        v-model="newServiceName"
                        placeholder="Enter service name"
                    />
                </div>
            </div>

            <DialogFooter class="gap-2">
                <Button variant="outline" @click="closeServiceDialog">Cancel</Button>
                <Button
                    :disabled="(!isCreatingNewService && !selectedServiceId) || (isCreatingNewService && !newServiceName.trim())"
                    @click="confirmAddService"
                >
                    Add Service
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
