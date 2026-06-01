<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { index, store } from '@/routes/briefs';
import { dashboard } from '@/routes';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Briefs', href: index() },
            { title: 'Upload', href: store() },
        ],
    },
});
</script>

<template>
    <Head title="Upload Brief" />

    <div class="w-full p-4">
        <Card>
            <CardHeader>
                <CardTitle>Upload Client Brief</CardTitle>
            </CardHeader>
            <CardContent>
                <Form
                    :action="store().url"
                    method="post"
                    v-slot="{ errors, processing }"
                    class="grid gap-4"
                    enctype="multipart/form-data"
                >
                    <div class="grid gap-2">
                        <Label for="title">Project Title</Label>
                        <Input id="title" name="title" required />
                        <InputError :message="errors.title" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="client_name">Client Name</Label>
                        <Input id="client_name" name="client_name" required />
                        <InputError :message="errors.client_name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="industry">Industry</Label>
                        <Input id="industry" name="industry" />
                        <InputError :message="errors.industry" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="budget">Budget (USD)</Label>
                            <Input
                                id="budget"
                                name="budget"
                                type="number"
                                min="0"
                                step="0.01"
                            />
                            <InputError :message="errors.budget" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="deadline">Deadline</Label>
                            <Input
                                id="deadline"
                                name="deadline"
                                type="date"
                            />
                            <InputError :message="errors.deadline" />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="notes">Internal Notes</Label>
                        <textarea
                            id="notes"
                            name="notes"
                            rows="3"
                            class="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs"
                        />
                        <InputError :message="errors.notes" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="brief_file">RFP / Brief PDF</Label>
                        <Input
                            id="brief_file"
                            name="brief_file"
                            type="file"
                            accept="application/pdf"
                            required
                        />
                        <InputError :message="errors.brief_file" />
                    </div>

                    <Button type="submit" :disabled="processing">
                        <Spinner v-if="processing" class="mr-2" />
                        Upload & Analyze
                    </Button>
                </Form>
            </CardContent>
        </Card>
    </div>
</template>
