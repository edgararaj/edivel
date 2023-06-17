<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router } from "@inertiajs/vue3";
import NavLink from "@/Components/NavLink.vue";
import { useStore } from "vuex";
import { computed } from "@vue/reactivity";
import { reactive, ref, onMounted, useAttrs } from "vue";
import { loadStripe } from "@stripe/stripe-js";

const store = useStore();
const attrs = useAttrs();
const cart = computed(() => store.state.cart)
const cardElement = ref(null);
const paymentProcessing = ref(false);

const customer = reactive(attrs.auth.user);
const stripe = reactive({});

const processPayment = async () => {
  paymentProcessing.value = true;

  const { paymentMethod, error } = await stripe.value.createPaymentMethod(
    'card', cardElement.value, {
    billing_details: {
      name: customer.name,
      email: customer.email,
      address: {
        line1: customer.address,
        city: customer.city,
        state: customer.state,
        postal_code: customer.postcode
      }
    }
  }
  );

  if (error) {
    paymentProcessing.value = false;
    console.error(error);
  } else {
    console.log(paymentMethod);
    customer.payment_method_id = paymentMethod.id;
    customer.amount = store.state.cart.reduce((acc, item) => acc + (item.price * item.quantity), 0);
    customer.cart = JSON.stringify(store.state.cart);

    axios.post('/api/purchase', customer)
      .then((response) => {
        paymentProcessing.value = false;
        console.log(response);

        store.commit('updateOrder', response.data);
        store.dispatch('clearCart');

        router.visit('/summary');
      })
      .catch((error) => {
        paymentProcessing.value = false;
        console.error(error);
      });
  }
};

const cartQuantity = computed(() => {
  return store.state.cart.reduce((acc, item) => acc + item.quantity, 0);
});

const cartTotal = computed(() => {
  let amount = store.state.cart.reduce((acc, item) => acc + (item.price * item.quantity), 0);
  amount = (amount / 100);

  return amount.toLocaleString('en-US', { style: 'currency', currency: 'EUR' });
});

const cartLineTotal = (item) => {
  let amount = item.price * item.quantity;
  amount = (amount / 100);

  return amount.toLocaleString('en-US', { style: 'currency', currency: 'EUR' });
};

onMounted(async () => {
  stripe.value = await loadStripe(import.meta.env.VITE_STRIPE_KEY)
  const elements = stripe.value.elements();
  cardElement.value = elements.create('card', {
    classes: {
      base: 'bg-gray-100 rounded border border-gray-300 focus:border-indigo-500 text-base outline-none text-gray-700 p-3 leading-8 transition-colors duration-200 ease-in-out'
    }
  });
  cardElement.value.mount('#card-element');
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

    <div class="w-full">
      <div class="lg:w-2/3 w-full mx-auto mt-8 overflow-auto">
        <table class="table-auto w-full text-left whitespace-no-wrap">
          <thead>
            <tr>
              <th
                class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-200 rounded-tl rounded-bl">
                Item</th>
              <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-200">Quantity</th>
              <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-200">Price</th>
              <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-200">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in cart" :key="item.id">
              <td class="p-4" v-text="item.name"></td>
              <td class="p-4" v-text="item.quantity"></td>
              <td class="p-4" v-text="cartLineTotal(item)"></td>
              <td class="w-10 text-right">
                <button
                  class="flex ml-auto text-sm text-white bg-indigo-500 border-0 py-2 px-6 focus:outline-none hover:bg-indigo-600 rounded"
                  @click="$store.commit('removeFromCart', index)">Remove</button>
              </td>
            </tr>
            <tr>
              <td class="p-4 font-bold">Total Amount</td>
              <td class="p-4 font-bold" v-text="cartQuantity"></td>
              <td class="p-4 font-bold" v-text="cartTotal"></td>
              <td class="w-10 text-right"></td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="lg:w-2/3 w-full mx-auto mt-8">
        <div class="flex flex-wrap -mx-2 mt-8">
          <div class="p-2 w-1/3">
            <div class="relative">
              <label for="first_name" class="leading-7 text-sm text-gray-600">Name</label>
              <input type="text" id="first_name" name="first_name"
                class="w-full bg-gray-100 rounded border border-gray-300 focus:border-indigo-500 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                v-model="customer.name" :disabled="paymentProcessing">
            </div>
          </div>
          <div class="p-2 w-1/3">
            <div class="relative">
              <label for="email" class="leading-7 text-sm text-gray-600">Email Address</label>
              <input type="email" id="email" name="email"
                class="w-full bg-gray-100 rounded border border-gray-300 focus:border-indigo-500 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                v-model="customer.email" :disabled="paymentProcessing">
            </div>
          </div>
        </div>
        <div class="flex flex-wrap -mx-2 mt-4">
          <div class="p-2 w-1/3">
            <div class="relative">
              <label for="address" class="leading-7 text-sm text-gray-600">Street Address</label>
              <input type="text" id="address" name="address"
                class="w-full bg-gray-100 rounded border border-gray-300 focus:border-indigo-500 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                v-model="customer.address" :disabled="paymentProcessing">
            </div>
          </div>
          <div class="p-2 w-1/3">
            <div class="relative">
              <label for="city" class="leading-7 text-sm text-gray-600">City</label>
              <input type="text" id="city" name="city"
                class="w-full bg-gray-100 rounded border border-gray-300 focus:border-indigo-500 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                v-model="customer.city" :disabled="paymentProcessing">
            </div>
          </div>
          <div class="p-2 w-1/6">
            <div class="relative">
              <label for="state" class="leading-7 text-sm text-gray-600">State</label>
              <input type="email" id="state" name="state"
                class="w-full bg-gray-100 rounded border border-gray-300 focus:border-indigo-500 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                v-model="customer.state" :disabled="paymentProcessing">
            </div>
          </div>
          <div class="p-2 w-1/6">
            <div class="relative">
              <label for="zip_code" class="leading-7 text-sm text-gray-600">Zip Code</label>
              <input type="email" id="zip_code" name="zip_code"
                class="w-full bg-gray-100 rounded border border-gray-300 focus:border-indigo-500 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                v-model="customer.zip_code" :disabled="paymentProcessing">
            </div>
          </div>
        </div>
        <div class="flex flex-wrap -mx-2 mt-4">
          <div class="p-2 w-full">
            <div class="relative">
              <label for="card-element" class="leading-7 text-sm text-gray-600">Credit Card Info</label>
              <div id="card-element"></div>
            </div>
          </div>
        </div>
        <div class="p-2 w-full">
          <button
            class="flex mx-auto text-white bg-indigo-500 border-0 py-2 px-8 focus:outline-none hover:bg-indigo-600 rounded text-lg"
            @click="processPayment" :disabled="paymentProcessing"
            v-text="paymentProcessing ? 'Processing' : 'Pay Now'"></button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
