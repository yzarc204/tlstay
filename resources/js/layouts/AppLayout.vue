<template>
  <div class="min-h-screen bg-neutral flex flex-col">
    <!-- Header -->
    <Header />
    
    <!-- Main Content -->
    <main class="flex-1">
      <slot />
    </main>
    
    <!-- Footer -->
    <Footer />
    
    <!-- Toast Container -->
    <Toaster />
    
    <!-- Confirm Dialog -->
    <ConfirmDialog />
    
    <!-- Flash Messages Handler -->
    <FlashToaster />
  </div>
</template>

<script setup>
import Header from '@/components/layouts/Header.vue'
import Footer from '@/components/layouts/Footer.vue'
import Toaster from '@/components/ui/Toaster.vue'
import ConfirmDialog from '@/components/ui/ConfirmDialog.vue'
import FlashToaster from '@/components/FlashToaster.vue'
</script>
