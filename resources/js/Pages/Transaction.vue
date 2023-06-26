<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Pagination from '@/Components/Pagination.vue'
import { Head } from "@inertiajs/vue3";
import NavLink from "@/Components/NavLink.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { useStore } from "vuex";
import { computed } from "@vue/reactivity";
import { Link } from '@inertiajs/vue3';

const process = () => {
  axios.get('/process-transaction')
    .then((response) => {
      console.log(response);
    })
    .catch((error) => {
      console.error(error);
    });
}

</script>

<template>
  <Head title="Transaction" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
          Transaction
        </h2>
      </div>
    </template>

    <section class="text-gray-700 body-font">
      <div class="container px-5 py-24 mx-auto">
        <div id="paypal-button-container"></div>
      </div>
    </section>

    <component :is="'script'">
      paypal.Buttons({
          // Order is created on the server and the order id is returned
          createOrder() {
            return fetch("/my-server/create-paypal-order", {
              method: "POST",
              headers: {
                "Content-Type": "application/json",
              },

              // use the "body" param to optionally pass additional order information
              // like product skus and quantities
              body: JSON.stringify({
                cart: [
                  {
                    sku: "YOUR_PRODUCT_STOCK_KEEPING_UNIT",
                    quantity: "YOUR_PRODUCT_QUANTITY",
                  },
                ],
              }),
            })
            .then((response) => response.json())
            .then((order) => order.id);
          },

          // Finalize the transaction on the server after payer approval
          onApprove(data) {
            return fetch("/my-server/capture-paypal-order", {
              method: "POST",
              headers: {
                "Content-Type": "application/json",
              },

              body: JSON.stringify({
                orderID: data.orderID
              })
            })
            .then((response) => response.json())
            .then((orderData) => {
              // Successful capture! For dev/demo purposes:
              console.log('Capture result', orderData, JSON.stringify(orderData, null, 2));
              const transaction = orderData.purchase_units[0].payments.captures[0];
              alert(`Transaction ${transaction.status}: ${transaction.id}\n\nSee console for all available details`);
              // When ready to go live, remove the alert and show a success message within this page. For example:
              // const element = document.getElementById('paypal-button-container');
              // element.innerHTML = '<h3>Thank you for your payment!</h3>';
              // Or go to another URL:  window.location.href = 'thank_you.html';
            });
          }
        }).render('#paypal-button-container');
    </component>

  </AuthenticatedLayout>
</template>
