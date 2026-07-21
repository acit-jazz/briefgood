<script setup lang="ts">
import { Form, Head, setLayoutProps } from '@inertiajs/vue3';
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { dashboard } from '@/routes';
import { index as usersIndex, update as usersUpdate } from '@/routes/users';

type Role = {
    value: string;
    label: string;
};

type BusinessUnit = {
    id: string;
    name: string;
};

const props = defineProps<{
    user: {
        id: string;
        name: string;
        email: string;
        role: string;
        business_unit?: { id: string; name: string } | null;
    };
    roles: Role[];
    businessUnits: BusinessUnit[];
}>();

const isEdit = computed(() => !!props.user?.id);

setLayoutProps({
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Users', href: usersIndex() },
        {
            title: isEdit.value ? 'Edit' : 'Create',
            href: '#',
        },
    ],
});

function getRoleLabel(roleValue: string): string {
    const role = props.roles.find(r => r.value === roleValue);

    return role?.label || roleValue;
}
</script>

<template>
    <Head :title="isEdit ? 'Edit User' : 'Create User'" />

    <div class="w-full p-4">
        <Card>
            <CardHeader>
                <CardTitle>{{ isEdit ? 'Edit' : 'Create' }} User</CardTitle>
            </CardHeader>
            <CardContent>
                <Form
                    :action="isEdit ? usersUpdate(user!.id!).url : '#'"
                    :method="isEdit ? 'put' : 'post'"
                    v-slot="{ errors, processing }"
                    class="grid gap-4"
                >
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input
                            id="name"
                            name="name"
                            :default-value="user?.name"
                            required
                        />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email</Label>
                        <Input
                            id="email"
                            name="email"
                            type="email"
                            :default-value="user?.email"
                            required
                        />
                        <InputError :message="errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="role">Role</Label>
                        <select
                            id="role"
                            name="role"
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm"
                        >
                            <option
                                v-for="role in roles"
                                :key="role.value"
                                :value="role.value"
                                :selected="role.value === user?.role"
                            >
                                {{ role.label }}
                            </option>
                        </select>
                        <InputError :message="errors.role" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="business_unit_id">Business Unit</Label>
                        <select
                            id="business_unit_id"
                            name="business_unit_id"
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm"
                        >
                            <option value="">Select Business Unit</option>
                            <option
                                v-for="bu in businessUnits"
                                :key="bu.id"
                                :value="bu.id"
                                :selected="bu.id === user?.business_unit?.id"
                            >
                                {{ bu.name }}
                            </option>
                        </select>
                        <InputError :message="errors.business_unit_id" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password">{{ isEdit ? 'New Password (leave blank to keep)' : 'Password' }}</Label>
                        <Input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="new-password"
                        />
                        <InputError :message="errors.password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password_confirmation">Confirm Password</Label>
                        <Input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            autocomplete="new-password"
                        />
                        <InputError :message="errors.password_confirmation" />
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
