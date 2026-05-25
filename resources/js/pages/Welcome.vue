<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard, login } from '@/routes';
import { register } from '@/routes';
import Grainient from "./Grainient.vue";
</script>

<template>
    <Head title="Welcome">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div class="absolute left-0 top-0 h-full w-full">
        <Grainient
        color1="#5227FF"
        color2="#FF9FFC"
        color3="#B19EEF"
        :time-speed="0.25"
        :color-balance="0.0"
        :warp-strength="1.0"
        :warp-frequency="5.0"
        :warp-speed="2.0"
        :warp-amplitude="50.0"
        :blend-angle="0.0"
        :blend-softness="0.05"
        :rotation-amount="500.0"
        :noise-scale="2.0"
        :grain-amount="0.1"
        :grain-scale="2.0"
        :grain-animated="false"
        :contrast="1.5"
        :gamma="1.0"
        :saturation="1.0"
        :center-x="0.0"
        :center-y="0.0"
        :zoom="0.9"
        />
        <div class="absolute left-0 top-0 w-full h-full bg-[rgba(0,0,0,0.6)]"></div>
    </div>
    <div
        class="flex min-h-screen relative z-10 flex-col items-center p-6 text-white lg:justify-center lg:p-8"
    >
        <div
            class="flex w-full items-center justify-center opacity-100 transition-opacity duration-750 lg:grow starting:opacity-0"
        >
            <main
                class="w-full"
            >
             <h1 class="text-5xl mb-5 font-extrabold lexed-deca text-center">BriefGood</h1>
             <p class="text-lg text-center text-white max-w-4xl mx-auto">
                 AI-powered brief standardization platform for enterprise groups and agencies — automate client brief analysis, smart business unit matching, pitch distribution, and resource planning in one centralized workflow.
             </p>
            <nav class="flex items-center w-fit mx-auto py-10  gap-4">
                <Link
                    v-if="$page.props.auth.user"
                    :href="dashboard()"
                    class="inline-block rounded-sm border border-[#fffff] px-5 py-1.5 text-sm leading-normal text-white hover:border-[#fffff] "
                >
                    Dashboard
                </Link>
                <template v-else>
                    <Link
                        :href="login()"
                        class="inline-block rounded-sm border border-[#fffff] px-5 py-1.5 text-sm leading-normal text-white hover:border-[#fffff] hover:bg-black"
                    >
                        Log in
                    </Link>
                </template>
            </nav>
            </main>
        </div>
        <div class="hidden h-14.5 lg:block"></div>
    </div>
</template>
