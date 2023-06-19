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

    <script src="https://www.paypal.com/sdk/js?client-id=VKG82CHHETDLQ&currency=EUR"></script>

    <section class="text-gray-700 body-font">
      <div class="container px-5 py-24 mx-auto">
        <Link :href="route('processTransaction')">
          bota
        </Link>
      </div>
    </section>
  </AuthenticatedLayout>
</template>
