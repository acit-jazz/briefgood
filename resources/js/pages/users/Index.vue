<script setup lang="ts">
import { computed } from 'vue';
import { Head, router, setLayoutProps, usePage } from '@inertiajs/vue3';
import { Pencil, Trash2 } from 'lucide-vue-next';
import usersRoute from '@/routes/users';
import { dashboard } from '@/routes';

type UserItem = {
    id: string;
    name: string;
    email: string;
    role: string;
    role_label: string;
    business_unit?: { id: string; name: string } | null;
    created_at: string;
};

const page = usePage();
const auth = computed(() => page.props.auth);

// Role check helpers
const isSuperAdmin = computed(() => auth.value?.user?.role === 'super_admin');
const isGroupAdmin = computed(() => auth.value?.user?.role === 'group_admin');
const canManageUsers = computed(() => isSuperAdmin.value || isGroupAdmin.value);

const props = defineProps<{
    users: { data: UserItem[] };
}>();

setLayoutProps({
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Users', href: usersRoute.index() },
    ],
});

function deleteUser(id: string) {
    if (confirm('Are you sure you want to delete this user?')) {
        router.delete(usersRoute.destroy(id).url);
    }
}

function goToEdit(id: string) {
    router.visit(usersRoute.edit(id).url);
}
</script>

<template>
    <Head title="Users" />

    <div class="flex flex-col gap-6 p-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Users</h1>
            <p class="text-sm text-muted-foreground">
                Manage registered users and their roles.
            </p>
        </div>

        <!-- Debug: show raw data -->
        <!-- <pre>{{ users }}</pre> -->

        <div v-if="users?.data?.length" class="rounded-lg border">
            <table class="min-w-full divide-y divide-border">
                <thead class="bg-muted/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-medium">Name</th>
                        <th class="px-4 py-3 text-left text-sm font-medium">Email</th>
                        <th class="px-4 py-3 text-left text-sm font-medium">Role</th>
                        <th class="px-4 py-3 text-left text-sm font-medium">Business Unit</th>
                        <th class="px-4 py-3 text-left text-sm font-medium">Created</th>
                        <th class="px-4 py-3 text-right text-sm font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border bg-white">
                    <tr
                        v-for="user in users.data"
                        :key="user.id"
                        class="hover:bg-muted/50"
                    >
                        <td class="px-4 py-3 text-sm font-medium">{{ user.name }}</td>
                        <td class="px-4 py-3 text-sm text-muted-foreground">{{ user.email }}</td>
                        <td class="px-4 py-3 text-sm">
                            <span
                                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                :class="{
                                    'bg-purple-100 text-purple-800': user.role === 'super_admin',
                                    'bg-blue-100 text-blue-800': user.role === 'group_admin',
                                    'bg-green-100 text-green-800': user.role === 'business_unit_pic',
                                    'bg-gray-100 text-gray-800': user.role === 'viewer',
                                }"
                            >
                                {{ user.role_label }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-muted-foreground">
                            {{ user.business_unit?.name || '-' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-muted-foreground">
                            {{ user.created_at }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button
                                    v-if="canManageUsers"
                                    @click="goToEdit(user.id)"
                                    class="rounded p-1 hover:bg-muted"
                                    title="Edit"
                                >
                                    <Pencil class="size-4 text-muted-foreground" />
                                </button>
                                <button
                                    v-if="isSuperAdmin"
                                    @click="deleteUser(user.id)"
                                    class="rounded p-1 hover:bg-muted"
                                    title="Delete"
                                >
                                    <Trash2 class="size-4 text-destructive" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p
            v-else
            class="rounded-lg border border-dashed p-8 text-center text-sm text-muted-foreground"
        >
            No users found.
        </p>
    </div>
</template>
