<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import NavLink from "@/Components/NavLink.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { useStore } from "vuex";
import { computed } from "@vue/reactivity";
import { Link, router } from '@inertiajs/vue3';
import OptionToggle from '@/Components/OptionToggle.vue'
import { ref } from 'vue'
import { watch } from "vue";
import QuantityInput from '@/Components/QuantityInput.vue'

// const products = computed(() => store.state.products)

const quantity = ref(1)

const formatCurrency = ((price) => {
  price = (price / 100);
  return price.toLocaleString('en-US', { style: 'currency', currency: 'EUR' });
});

const props = defineProps({
  product: {},
  allColors: Array,
  allSizes: Array,
  colors: Array,
  sizes: Array,
  price: {},
  options: {}
});

let currentColor = null;
let currentSize = null;

const sizeChanged = (value) => {
  currentSize = value
  router.get(route('products.show', { slug: props.product.slug }), { currentColor: currentColor, size: value, get: 1 }, { preserveState: true, preserveScroll: true })
}

const colorChanged = (value) => {
  currentColor = value
  router.get(route('products.show', { slug: props.product.slug }), { currentSize: currentSize, color: value, get: 1 }, { preserveState: true, preserveScroll: true })
}

</script>

<template>
  <Head title="Product" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
          Product
        </h2>
        <NavLink href="/posts/create">New Post</NavLink>
      </div>
    </template>

    <section class="text-gray-700 body-font overflow-hidden" v-if="product">
      <div class="container px-12 py-24 mx-auto">
        <div class="lg:w-3/5 mx-auto flex flex-wrap">
          <img alt="ecommerce" class="lg:w-1/2 w-full lg:h-auto h-64 object-cover object-center rounded"
            src="https://dummyimage.com/640x640">
          <div class="lg:w-1/2 w-full lg:pl-10 lg:py-0 py-6 lg:mt-0 mt-6">
            <h1 class="text-gray-900 text-3xl title-font mb-2 font-extrabold" v-text="product.name"></h1>
            <div class="flex items-center">
              <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                  d="M10 19.625C4.6875 19.625 0.40625 15.3125 0.40625 10C0.40625 4.6875 4.6875 0.40625 10 0.40625C15.3125 0.40625 19.625 4.6875 19.625 10C19.625 15.3125 15.3125 19.625 10 19.625ZM10 1.5C5.3125 1.5 1.5 5.3125 1.5 10C1.5 14.6875 5.3125 18.5313 10 18.5313C14.6875 18.5313 18.5313 14.6875 18.5313 10C18.5313 5.3125 14.6875 1.5 10 1.5Z"
                  fill="#13C296" />
                <path
                  d="M8.9375 12.1875C8.71875 12.1875 8.53125 12.125 8.34375 11.9687L6.28125 9.96875C6.0625 9.75 6.0625 9.40625 6.28125 9.1875C6.5 8.96875 6.84375 8.96875 7.0625 9.1875L8.9375 11.0312L12.9375 7.15625C13.1562 6.9375 13.5 6.9375 13.7188 7.15625C13.9375 7.375 13.9375 7.71875 13.7188 7.9375L9.5625 12C9.34375 12.125 9.125 12.1875 8.9375 12.1875Z"
                  fill="#13C296" />
              </svg>
              <p class="ml-2">In Stock</p>
            </div>

            <p class="leading-relaxed" v-text="product.description"></p>
            <h1 class="mt-5 font-bold text-lg">
              Choose Color
            </h1>
            <OptionToggle :values="colors" :allValues="allColors" :selected="options.color"
              :updateState="options.size !== null" :colorOption="true" @change="colorChanged">
            </OptionToggle>
            <h1 class="mt-5 font-bold text-lg">
              Choose Size
            </h1>
            <OptionToggle :values="sizes" :allValues="allSizes" :selected="options.size"
              :updateState="options.color !== null" @change="sizeChanged">
            </OptionToggle>
            <div class="flex mt-6 pt-4 items-center justify-between">
              <div>
                <h1 class="font-bold">
                  Quantity
                </h1>
                <QuantityInput v-model="quantity"></QuantityInput>
              </div>
              <span class="title-font font-extrabold text-2xl text-gray-900"
                v-text="formatCurrency(product.price)"></span>
            </div>
            <button
              class="flex w-full text-white bg-indigo-500 border-0 py-2 px-6 mt-4 focus:outline-none hover:bg-indigo-600 rounded"
              @click="$store.commit('addToCart', product, quantity)">
              <h1 class="ml-auto mr-auto">Add To Cart</h1>
            </button>
          </div>
        </div>
      </div>
    </section>
  </AuthenticatedLayout>
</template>
