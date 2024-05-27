<template>
    <div class="example-component">
        <h1>Hello from Vue!</h1>
    </div>
</template>

<script>
export default {
    mounted() {
        console.log('Component mounted.');
    }
}
</script>

<style scoped>
.example-component {
    font-family: Arial, sans-serif;
}
</style>