<template>
    <h1>Hello {{ name }}!</h1>
</template>

<script setup>
    defineProps({
        name: String
    });
</script>
