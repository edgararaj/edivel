<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Pagination from '@/Components/Pagination.vue'
import { Head } from "@inertiajs/vue3";
import NavLink from "@/Components/NavLink.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { useStore } from "vuex";
import { computed } from "@vue/reactivity";
import { Link } from '@inertiajs/vue3';

const store = useStore();
// const products = computed(() => store.state.products)

const formatCurrency = ((price) => {
  price = (price / 100);
  return price.toLocaleString('en-US', { style: 'currency', currency: 'EUR' });
});

defineProps({
  products: {
    type: Object
  }
});
</script>

<template>
  <Head title="Products" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
          Dashboard
        </h2>
        <NavLink href="/posts/create">New Post</NavLink>
      </div>
    </template>

    <section class="text-gray-700 body-font">
      <div class="container px-5 py-24 mx-auto">
        <div class="flex flex-wrap -m-4" v-if="!products.data.length">
          <div class="lg:w-1/4 md:w-1/2 p-4 w-full mb-4">
            <a class="block relative h-48 rounded overflow-hidden">
              <img alt="ecommerce" class="object-cover object-center w-full h-full block"
                src="https://dummyimage.com/420x260">
            </a>
            <div class="mt-4">
              <h3 class="text-gray-500 text-xs tracking-widest title-font mb-1 uppercase inline-block mr-2">N/A</h3>
              <h2 class="text-gray-900 title-font text-lg font-medium">Loading</h2>
              <p class="mt-1">$0.00</p>
            </div>
          </div>
        </div>
        <div class="flex flex-wrap -m-4" v-else>
          <div class="lg:w-1/4 md:w-1/2 p-4 w-full mb-4" v-for="product in products.data" :key="product.id">
            <Link class="block relative h-48 rounded overflow-hidden" :href="route('products.show', [product.variants[0].slug])">
            <img alt="ecommerce" class="object-cover object-center w-full h-full block"
              src="https://dummyimage.com/420x260">
            </Link>
            <div class="mt-4">
              <h3 class="text-gray-500 text-xs tracking-widest title-font mb-1 uppercase inline-block mr-2"
                v-for="category in product.categories" v-text="category.name"></h3>
              <h2 class="text-gray-900 title-font text-lg font-medium" v-text="product.name"></h2>
              <p class="mt-1" v-text="formatCurrency(product.variants[0].price)"></p>
            </div>
          </div>
        </div>
        <pagination :links="products.links" />
      </div>
    </section>
  </AuthenticatedLayout>
</template>
