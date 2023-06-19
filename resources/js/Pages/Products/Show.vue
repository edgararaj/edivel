<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
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
  return price.toLocaleString('en-US', {style: 'currency', currency: 'EUR'});
});

defineProps({
  product: {}
});
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
                <img alt="ecommerce" class="lg:w-1/2 w-full lg:h-auto h-64 object-cover object-center rounded" src="https://dummyimage.com/640x640">
                <div class="lg:w-1/2 w-full lg:pl-10 lg:py-6 mt-6 lg:mt-0">
                    <h2
                        class="text-sm title-font text-gray-500 tracking-widest uppercase inline-block mr-2"
                        v-for="category in product.categories"
                        v-text="category.name"
                    ></h2>
                    <h1
                        class="text-gray-900 text-3xl title-font font-medium mb-2"
                        v-text="product.name"
                    ></h1>
                    <p
                        class="leading-relaxed"
                        v-text="product.description"
                    ></p>
                    <div class="flex mt-6 pt-4 border-t-2 border-gray-200">
                        <span
                            class="title-font font-medium text-2xl text-gray-900"
                            v-text="formatCurrency(product.price)"
                        ></span>
                        <button
                            class="flex ml-auto text-white bg-indigo-500 border-0 py-2 px-6 focus:outline-none hover:bg-indigo-600 rounded"
                            @click="$store.commit('addToCart', product)"
                        >Add To Cart</button>
                    </div>
                </div>
            </div>
        </div>
    </section>
  </AuthenticatedLayout>
</template>