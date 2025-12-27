<template>
  <Head title="Trang chủ" />
  <AppLayout>
    <div class="home">
      <!-- Slider Section with Search Box -->
      <section class="relative">
        <HeroSlider :sliders="sliders" />
        <SearchBox :wards="wards" :streets="streets" />
      </section>

      <!-- About Section -->
      <AboutSection />

      <!-- Featured Properties -->
      <FeaturedProperties :featured-houses="featuredHouses" />

      <!-- Services Section -->
      <ServicesSection />

      <!-- CTA Section -->
      <CTASection />
    </div>
  </AppLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import HeroSlider from '@/components/home/HeroSlider.vue'
import SearchBox from '@/components/home/SearchBox.vue'
import AboutSection from '@/components/home/AboutSection.vue'
import FeaturedProperties from '@/components/home/FeaturedProperties.vue'
import ServicesSection from '@/components/home/ServicesSection.vue'
import CTASection from '@/components/home/CTASection.vue'

defineProps({
  featuredHouses: {
    type: Array,
    default: () => [],
  },
  sliders: {
    type: Array,
    default: () => [],
  },
  wards: {
    type: Array,
    default: () => [],
  },
  streets: {
    type: Array,
    default: () => [],
  },
})
</script>
