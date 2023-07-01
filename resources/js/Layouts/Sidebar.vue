<template>
  <div x-data="setup()" x-init="$refs.loading.classList.add('hidden');" :class="{ 'dark': isDark }"
    @resize.window="watchScreen()">
    <div class="flex h-screen antialiased text-gray-900 bg-gray-100 dark:bg-dark dark:text-light">
      <!-- Sidebar first column -->
      <!-- Backdrop -->
      <div v-show="isSidebarOpen" @click="isSidebarOpen = false" class="fixed inset-0 z-10 bg-indigo-800 "
        style="opacity: 0.5" aria-hidden="true"></div>

      <aside v-show="isSidebarOpen" x-transition:enter="transition-all transform duration-300 ease-in-out"
        x-transition:enter-start="-translate-x-full opacity-0" x-transition:enter-end="translate-x-0 opacity-100"
        x-transition:leave="transition-all transform duration-300 ease-in-out"
        x-transition:leave-start="translate-x-0 opacity-100" x-transition:leave-end="-translate-x-full opacity-0"
        x-ref="sidebar" @keydown.escape="isSidebarOpen = false" tabindex="-1"
        class="fixed inset-y-0 z-10 flex-shrink-0 w-96 bg-white border-l right-0  dark:border-indigo-800 dark:bg-darker focus:outline-none">
        <div class="flex flex-col h-full">
          <!-- Sidebar links -->
          <nav aria-label="Main" class="flex-1 px-2 py-4 space-y-2 overflow-y-hidden hover:overflow-y-auto">
            <div class="mx-5">
              <div class="flex justify-between items-center border-b border-gray-300 py-5">
                <h1 class="font-extrabold text-2xl">Cart</h1>
                <button @click="isSidebarOpen = false">
                  <svg class='w-6 h-6' xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    strokeWidth={1.5} stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
              <ul v-for="(item, index) in cart">
                <li class="flex items-center mt-5">
                  <img alt="ecommerce" class="w-15 h-20 object-cover object-center rounded"
                    src="https://dummyimage.com/640x640">
                  <div class="ml-5 w-full">
                    <h1 v-text="item.name"></h1>
                    <div class="flex items-center justify-between mt-2">
                      <QuantityInput v-model="item.quantity" @remove="$store.commit('removeFromCart', index)"></QuantityInput>
                      <h1>{{ formatCurrency(item.price) }}</h1>
                    </div>
                  </div>
                </li>
              </ul>
            </div>
          </nav>

          <div class="flex flex-col px-6 py-10 border-t">
            <div class="font-bold flex items-center justify-between ">

              <h1 class="uppercase">Subtotal</h1>
              <p v-text="cartTotal"></p>
            </div>
            <p class="text-xs mt-6 text-center">
              Shipping, taxes, and discount codes calculated at checkout.
            </p>
            <Link
              :href="route('checkout')"
              class="flex text-white bg-indigo-500 border-0 py-2 px-6 mt-4 focus:outline-none hover:bg-indigo-600 rounded">
              <h1 class="ml-auto mr-auto">Checkout</h1>
          </Link>
          </div>
        </div>
      </aside>

      <!-- Sidebars buttons -->
      <!-- <div class="fixed flex items-center space-x-4 top-5 right-10 lg:hidden">
                <button @click="isSidebarOpen = true; $nextTick(() => { $refs.sidebar.focus() })"
                    class="p-1 text-indigo-400 transition-colors duration-200 rounded-md bg-indigo-50 hover:text-indigo-600 hover:bg-indigo-100 dark:hover:text-light dark:hover:bg-indigo-700 dark:bg-dark focus:outline-none focus:ring">
                    <span class="sr-only">Toggle main manu</span>
                    <span aria-hidden="true">
                        <svg v-show="!isSidebarOpen" class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg v-show="isSidebarOpen" class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </span>
                </button>
            </div> -->

      <!-- Main content -->
      <main class="flex-1 overflow-x-auto">
        <slot></slot>
      </main>
    </div>
  </div>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
import QuantityInput from "@/Components/QuantityInput.vue";
import { ref, onMounted, computed } from "vue"
import { useStore } from "vuex";

const props = defineProps(['modelValue'])
const emits = defineEmits(['update:modelValue'])

const isSidebarOpen = computed({
  get() {
    return props.modelValue
  },
  set(value) {
    emits('update:modelValue', value)
  }
})

const formatCurrency = ((price) => {
  price = (price / 100);
  return price.toLocaleString('en-US', { style: 'currency', currency: 'EUR' });
});

const store = useStore();
const cart = computed(() => store.state.cart)

const cartTotal = computed(() => {
  let amount = store.state.cart.reduce((acc, item) => acc + (item.price * item.quantity), 0);
  amount = (amount / 100);

  return amount.toLocaleString('en-US', { style: 'currency', currency: 'EUR' });
});
</script>
