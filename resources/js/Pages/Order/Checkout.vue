<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router } from "@inertiajs/vue3";
import NavLink from "@/Components/NavLink.vue";
import { useStore } from "vuex";
import { computed } from "@vue/reactivity";
import { reactive, ref, onMounted, useAttrs } from "vue";
import { loadStripe } from "@stripe/stripe-js";
import OptionToggle from '@/Components/OptionToggle.vue'

const store = useStore();
const attrs = useAttrs();
const cart = computed(() => store.state.cart)
const cardElement = ref(null);
const paymentProcessing = ref(false);

const formatCurrency = ((price) => {
  price = (price / 100);
  return price.toLocaleString('en-US', { style: 'currency', currency: 'EUR' });
});

const customer = reactive(attrs.auth.user);
const stripe = reactive({});

const paymentOptions = ['Stripe', 'Paypal']
const currentPaymentOption = ref('Stripe')

const paymentOptionChanged = (e) => {
  currentPaymentOption.value = e
}

const processPayment = async () => {
  paymentProcessing.value = true;

  if (currentPaymentOption.value == "Stripe") {
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

      axios.post('/purchase', customer)
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
  }
  else {
    router.get(route('processTransaction'))
      .then((response) => {
        paymentProcessing.value = false;
        console.log(response);
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
    <!-- <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
          Product
        </h2>
        <NavLink href="/posts/create">New Post</NavLink>
      </div>
    </template> -->

    <div class="w-full">
      <div class="lg:w-2/3 mx-auto mt-10 lg:mt-16">
        <h1 class="text-3xl font-extrabold">Checkout</h1>
        <p class="text-gray-600 mt-2">There are 3 products in your cart</p>
        <div class="flex flex-wrap flex-col-reverse lg:flex-row lg:mt-4">
          <div class="lg:w-3/5 w-full mx-auto mt-12 lg:mt-8">
            <h3 class="font-bold text-xl">Billing Details</h3>
            <div class="flex flex-wrap -mx-2 mt-2">
              <div class="p-2 w-1/3">
                <div class="relative">
                  <label for="first_name" class="leading-7 text-sm text-gray-600">First Name</label>
                  <input type="text" id="first_name" name="first_name"
                    class="w-full bg-gray-100 rounded border border-gray-300 focus:border-indigo-500 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                    v-model="customer.first_name" :disabled="paymentProcessing">
                </div>
              </div>
              <div class="p-2 w-1/3">
                <div class="relative">
                  <label for="last_name" class="leading-7 text-sm text-gray-600">Last Name</label>
                  <input type="text" id="last_name" name="last_name"
                    class="w-full bg-gray-100 rounded border border-gray-300 focus:border-indigo-500 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                    v-model="customer.last_name" :disabled="paymentProcessing">
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
                  <label for="info" class="leading-7 text-sm text-gray-600">Additional Information</label>
                  <textarea type="email" id="info" name="info" rows="3"
                    class="w-full bg-gray-100 rounded border border-gray-300 focus:border-indigo-500 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                    v-model="customer.info" :disabled="paymentProcessing">
                    </textarea>
                </div>
              </div>
            </div>
            <div class="flex items-center mb-5 mt-5">
              <input id="default-checkbox" type="checkbox" value=""
                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
              <label for="default-checkbox" class="ml-2 text-sm">Create account</label>
            </div>
            <div class="flex items-center">
              <input checked id="checked-checkbox" type="checkbox" value=""
                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
              <label for="checked-checkbox" class="ml-2 text-sm">Ship to a different address</label>
            </div>
            <OptionToggle :values="paymentOptions" :allValues="paymentOptions" :selected="paymentOptions[0]"
              @change="paymentOptionChanged" class="mt-5">
            </OptionToggle>
            <div class="flex flex-wrap -mx-2 mt-4" v-show="currentPaymentOption == 'Stripe'">
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
          <div class="lg:w-2/5 lg:pl-16 mt-8">
            <h3 class="font-bold text-xl">Your order</h3>
            <div class="w-full mx-auto lg:mt-8 mt-4 overflow-auto shadow-md rounded">
              <table class="table-auto w-full text-left whitespace-no-wrap">
                <thead>
                  <tr>
                    <th class="px-5 py-4 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100"> Item
                    </th>
                    <th
                      class="px-5 py-4 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100 text-right">
                      Price</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(item, index) in cart" :key="item.id" class="border-b">
                    <td class="p-5 flex items-center">
                      <img alt="ecommerce" class="w-16 h-16 object-cover object-center rounded"
                        src="https://dummyimage.com/640x640">
                      <div class="ml-5">
                        <h1 v-text="item.name"></h1>
                        <div class="flex space-x-8 mt-1">
                          <p class="text-gray-500">Brown</p>
                          <p class="text-gray-500">XL</p>
                          <p class="text-gray-500">1 X €36.00</p>
                        </div>
                      </div>
                    </td>
                    <td class="p-5 text-right" v-text="cartTotal"> </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="w-full mx-auto lg:mt-8 mt-4 overflow-auto shadow-md rounded">
              <div class="px-5 py-4 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">
                Payment
              </div>
              <div class="mx-5">
                <div class="flex items-center my-5">
                  <input id="default-checkbox" type="checkbox" value=""
                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                  <label for="default-checkbox" class="ml-2 text-sm">Direct Bank Transfer</label>
                </div>
                <div class="flex items-center my-5">
                  <input id="default-checkbox" type="checkbox" value=""
                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                  <label for="default-checkbox" class="ml-2 text-sm">Cash On Delivery</label>
                </div>
                <div class="flex items-center my-5">
                  <input id="default-checkbox" type="checkbox" value=""
                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                  <label for="default-checkbox" class="ml-2 text-sm">Online Getway</label>
                </div>
                <div class="flex space-x-4 mb-5">
                  <svg width="71" height="17" viewBox="0 0 71 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_121_51931)">
                      <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M6.12283 4.00159H10.9177C13.4922 4.00159 14.4613 5.3049 14.3115 7.21964C14.064 10.3808 12.153 12.1297 9.61813 12.1297H8.33834C7.99054 12.1297 7.75661 12.3599 7.66255 12.9837L7.11919 16.6102C7.0833 16.8453 6.95952 16.9815 6.77387 17.0001H3.76127C3.47783 17.0001 3.37758 16.7835 3.45184 16.3144L5.28861 4.68852C5.3604 4.22314 5.61537 4.00159 6.12283 4.00159Z"
                        fill="#009EE3" />
                      <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M26.9403 3.78613C28.558 3.78613 30.0507 4.66367 29.8465 6.85071C29.5989 9.44991 28.2065 10.8881 26.0096 10.8943H24.0899C23.8138 10.8943 23.6802 11.1196 23.6084 11.5813L23.2371 13.9416C23.1814 14.298 22.9982 14.4738 22.7284 14.4738H20.9423C20.6577 14.4738 20.5587 14.2918 20.6218 13.8846L22.0959 4.42479C22.1689 3.95941 22.3434 3.78613 22.6615 3.78613H26.9366H26.9403ZM24.0317 8.85086H25.486C26.3957 8.8162 26.9997 8.18621 27.0604 7.04998C27.0975 6.3482 26.6235 5.84569 25.8697 5.8494L24.5008 5.85559L24.0317 8.85086ZM34.702 13.7497C34.8654 13.6012 35.0313 13.5245 35.0077 13.7076L34.9496 14.1458C34.9199 14.3748 35.0102 14.4961 35.2231 14.4961H36.8099C37.0772 14.4961 37.2072 14.3884 37.2728 13.975L38.2506 7.83841C38.3001 7.53022 38.2246 7.37922 37.9906 7.37922H36.2455C36.0883 7.37922 36.0115 7.46709 35.9707 7.70721L35.9063 8.08471C35.8729 8.28151 35.7825 8.31617 35.6984 8.11813C35.4026 7.41759 34.6476 7.10321 33.5943 7.12796C31.1473 7.17871 29.4974 9.03652 29.3204 11.4179C29.1843 13.2596 30.5037 14.7065 32.2439 14.7065C33.5064 14.7065 34.0708 14.3352 34.707 13.7534L34.702 13.7497ZM33.3727 12.8054C32.3194 12.8054 31.5855 11.9649 31.7377 10.9352C31.8899 9.90539 32.8752 9.06498 33.9285 9.06498C34.9817 9.06498 35.7157 9.90539 35.5635 10.9352C35.4112 11.9649 34.4272 12.8054 33.3727 12.8054ZM41.356 7.35941H39.7469C39.4152 7.35941 39.2803 7.60695 39.3855 7.91143L41.3832 13.7609L39.4239 16.5445C39.2593 16.7772 39.3868 16.9888 39.6182 16.9888H41.4265C41.5319 17.001 41.6386 16.9826 41.7338 16.9359C41.8291 16.8892 41.9089 16.816 41.9637 16.7252L48.1077 7.91267C48.2971 7.64161 48.208 7.35694 47.8973 7.35694H46.1855C45.8922 7.35694 45.7746 7.47328 45.6063 7.71711L43.0442 11.4303L41.8993 7.70845C41.8325 7.48318 41.6654 7.35941 41.3572 7.35941H41.356Z"
                        fill="#113984" />
                      <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M54.4991 3.78617C56.1168 3.78617 57.6095 4.66371 57.4053 6.85075C57.1578 9.44995 55.7653 10.8882 53.5684 10.8944H51.6499C51.3739 10.8944 51.2402 11.1196 51.1684 11.5813L50.7971 13.9416C50.7414 14.2981 50.5583 14.4738 50.2884 14.4738H48.5024C48.2177 14.4738 48.1187 14.2919 48.1818 13.8847L49.6584 4.42235C49.7315 3.95697 49.906 3.78369 50.2241 3.78369H54.4991V3.78617ZM51.5905 8.85089H53.0448C53.9545 8.81624 54.5585 8.18624 54.6192 7.05002C54.6563 6.34824 54.1823 5.84572 53.4285 5.84944L52.0596 5.85563L51.5905 8.85089ZM62.2609 13.7498C62.4242 13.6012 62.5901 13.5245 62.5666 13.7077L62.5084 14.1458C62.4787 14.3748 62.569 14.4961 62.7819 14.4961H64.3687C64.636 14.4961 64.766 14.3884 64.8316 13.975L65.8094 7.83844C65.8589 7.53025 65.7834 7.37925 65.5495 7.37925H63.8068C63.6496 7.37925 63.5728 7.46713 63.532 7.70725L63.4676 8.08475C63.4342 8.28154 63.3439 8.3162 63.2597 8.11817C62.9639 7.41762 62.2089 7.10324 61.1556 7.128C58.7086 7.17874 57.0587 9.03655 56.8817 11.4179C56.7456 13.2596 58.065 14.7065 59.8052 14.7065C61.0677 14.7065 61.6321 14.3352 62.2683 13.7535L62.2609 13.7498ZM60.9328 12.8054C59.8795 12.8054 59.1455 11.965 59.2978 10.9352C59.45 9.90543 60.4352 9.06502 61.4885 9.06502C62.5418 9.06502 63.2758 9.90543 63.1235 10.9352C62.9713 11.965 61.9861 12.8054 60.9328 12.8054ZM68.2514 14.506H66.4196C66.3877 14.5074 66.3559 14.5018 66.3265 14.4895C66.2971 14.4772 66.2707 14.4586 66.2493 14.435C66.2279 14.4113 66.212 14.3832 66.2027 14.3527C66.1934 14.3222 66.191 14.29 66.1955 14.2585L67.8046 4.06465C67.8199 3.99508 67.8584 3.93276 67.9137 3.88781C67.969 3.84287 68.0378 3.81795 68.1091 3.81711H69.9409C69.9727 3.81569 70.0045 3.82131 70.034 3.8336C70.0634 3.84588 70.0898 3.86451 70.1112 3.88815C70.1326 3.9118 70.1485 3.93988 70.1578 3.97039C70.1671 4.00091 70.1695 4.03309 70.1649 4.06465L68.5559 14.2585C68.541 14.3285 68.5028 14.3914 68.4474 14.4368C68.3921 14.4822 68.323 14.5075 68.2514 14.5085V14.506Z"
                        fill="#009EE3" />
                      <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M3.12427 0H7.92412C9.2757 0 10.8798 0.04332 11.9516 0.990171C12.6683 1.62264 13.0445 2.6289 12.9579 3.71314C12.6633 7.37801 10.4713 9.43138 7.53052 9.43138H5.16402C4.76052 9.43138 4.49441 9.69872 4.38054 10.4215L3.71961 14.6298C3.67629 14.9021 3.5587 15.063 3.34829 15.0828H0.386447C0.0584528 15.0828 -0.0578933 14.8352 0.0275089 14.2882L2.15637 0.799562C2.24178 0.257443 2.54007 0 3.12427 0Z"
                        fill="#113984" />
                      <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M4.44971 9.9945L5.28764 4.68842C5.36067 4.22304 5.61563 4.00024 6.1231 4.00024H10.918C11.7114 4.00024 12.3538 4.12402 12.8563 4.35299C12.3748 7.61561 10.2645 9.42762 7.50191 9.42762H5.13912C4.82226 9.42886 4.58957 9.58729 4.44971 9.9945Z"
                        fill="#172C70" />
                    </g>
                    <defs>
                      <clipPath id="clip0_121_51931">
                        <rect width="70.1697" height="17" fill="white" />
                      </clipPath>
                    </defs>
                  </svg>
                  <svg width="50" height="16" viewBox="0 0 50 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                      d="M35.2949 10.6485C35.3049 8.12762 33.2209 7.03922 31.6029 6.19352C30.5789 5.65872 29.7419 5.22132 29.7419 4.58062C29.7419 4.03452 30.2719 3.45632 31.3999 3.30702C32.7409 3.17472 34.0939 3.40902 35.3109 3.98542L36.0079 0.678411C34.8209 0.234331 33.5629 0.00473 32.2949 0C28.3829 0 25.6639 2.08342 25.6639 5.05972C25.6639 7.25932 27.6369 8.48292 29.1289 9.21042C30.6209 9.93802 31.2009 10.4501 31.1839 11.1115C31.1839 12.1367 29.9579 12.5996 28.8139 12.6166C27.4029 12.6346 26.0109 12.2935 24.7689 11.6245L24.0559 14.9315C25.4609 15.4786 26.9579 15.7536 28.4659 15.7413C32.6259 15.7413 35.3619 13.6909 35.3779 10.5162L35.2949 10.6485ZM24.5369 0.265511L21.2219 15.5599H17.2439L20.5589 0.265511H24.5369ZM41.2129 10.1855L43.3009 4.44842L44.5119 10.1855H41.2129ZM49.3189 15.6099H45.6389L45.1579 13.3281H40.1689L39.3559 15.6099H35.1789L41.1299 1.43902C41.4039 0.76439 42.0569 0.32125 42.7879 0.31464H46.1029L49.3189 15.6099ZM12.5189 15.5098L18.9509 0.21543H14.6409L10.5139 10.6154L8.85593 1.76972C8.70893 0.87021 7.92893 0.21165 7.01593 0.21543H0.269928L0.169922 0.6614C1.52292 0.92596 2.83791 1.35871 4.08191 1.95112C4.61191 2.19962 4.97092 2.70892 5.02692 3.29092L8.19293 15.5098H12.5189Z"
                      fill="#212B36" />
                  </svg>
                  <svg width="28" height="17" viewBox="0 0 28 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17.2668 1.68652H9.92285V14.6172H17.2668V1.68652Z" fill="#F26122" />
                    <path
                      d="M10.7186 8.15183C10.7046 5.65763 11.8696 3.29563 13.8766 1.74423C10.4416 -0.883989 5.5076 -0.502329 2.5426 2.62083C-0.422402 5.74413 -0.422402 10.5599 2.5426 13.6832C5.5076 16.8061 10.4416 17.1881 13.8766 14.5595C11.8696 13.0084 10.7046 10.6464 10.7186 8.15183Z"
                      fill="#EA1D25" />
                    <path
                      d="M27.4355 8.15177C27.4325 11.2728 25.6015 14.1192 22.7165 15.4833C19.8325 16.8474 16.4005 16.4906 13.8765 14.5642C17.5115 11.7793 18.1404 6.65627 15.2834 3.11627C14.8734 2.60357 14.4015 2.14149 13.8765 1.74004C16.4005 -0.186617 19.8325 -0.543566 22.7165 0.820664C25.6015 2.1849 27.4325 5.03127 27.4355 8.15177Z"
                      fill="#F69E1E" />
                  </svg>
                </div>
              </div>
            </div>
            <Link
              :href="route('checkout')"
              class="flex text-white bg-indigo-500 border-0 py-2 px-6 mt-4 focus:outline-none hover:bg-indigo-600 rounded">
              <h1 class="ml-auto mr-auto">Place an Order</h1>
          </Link>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
