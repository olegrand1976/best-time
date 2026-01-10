<script setup lang="ts">
definePageMeta({
  middleware: 'auth',
})

// Redirect to dashboard
await navigateTo('/dashboard')
</script>
