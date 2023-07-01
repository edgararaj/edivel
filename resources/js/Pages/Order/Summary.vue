<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import NavLink from "@/Components/NavLink.vue";
import { useStore } from "vuex";
import { computed } from "@vue/reactivity";
import { reactive, ref, onMounted, useAttrs } from "vue";
import { loadStripe } from "@stripe/stripe-js";

const cartLineTotal = (item) => {
  let amount = item.price * item.pivot.quantity;
  amount = (amount / 100);

  return amount.toLocaleString('en-US', { style: 'currency', currency: 'EUR' });
}
const store = useStore()
const order = computed(() => store.state.order)
const orderQuantity = computed(() => {
  return store.state.order.products.reduce((acc, item) => acc + item.pivot.quantity, 0);
});

const orderTotal = computed(() => {
  let amount = store.state.order.products.reduce((acc, item) => acc + (item.price * item.pivot.quantity), 0);
  amount = (amount / 100);

  return amount.toLocaleString('en-US', { style: 'currency', currency: 'EUR' });
});
</script>
<template>
  <Head title="Product" />

  <AuthenticatedLayout>
    <!-- <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
          Product
        </h2>
        <NavLink href="/posts/create">New Post</NavLink>
      </div>
    </template> -->
    <div class="w-full">
      <div class="lg:w-2/3 w-full mx-auto mt-8 overflow-auto">
        <h2 class="text-sm title-font text-gray-500 tracking-widest" v-text="'Transaction ID: ' + order.transaction_id">
        </h2>
        <h1 class="text-gray-900 text-3xl title-font font-medium mb-4">Thank you for your purchase</h1>
        <table class="table-auto w-full text-left whitespace-no-wrap">
          <thead>
            <tr>
              <th
                class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-200 rounded-tl rounded-bl">
                Item</th>
              <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-200">Quantity</th>
              <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-200">Price</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in order.products" :key="item.id">
              <td class="p-4" v-text="item.name"></td>
              <td class="p-4" v-text="item.pivot.quantity"></td>
              <td class="p-4" v-text="cartLineTotal(item)"></td>
            </tr>
            <tr>
              <td class="p-4 font-bold">Total Amount</td>
              <td class="p-4 font-bold" v-text="orderQuantity"></td>
              <td class="p-4 font-bold" v-text="orderTotal"></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
