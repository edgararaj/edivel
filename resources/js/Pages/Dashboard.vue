<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import NavLink from "@/Components/NavLink.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

defineProps({
  posts: {
    type: Array,
    required: true,
  },
});

// const posts = [
//   {
//     id: 1,
//     user: {
//       name: "Leilao Alexander",
//       email: "leslie.alexander@example.com",
//       imageUrl:
//         "https://images.unsplash.com/photo-1494790108377-be9c29b29330?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80",
//     }
//   },
// ];
</script>

<template>
  <Head title="Dashboard" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2
          class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
        >
          Dashboard
        </h2>
        <NavLink href="/post/create">New Post</NavLink>
      </div>
    </template>

    <div class="flex items-center justify-center">
      <ul role="list" class="divide-y divide-gray-100 max-w-7xl container px-6 mx-auto">
        <li
          v-for="post in posts"
          :key="post.id"
          class="flex justify-between gap-x-6 py-5"
        >
          <div class="flex gap-x-4">
            <img
              class="h-12 w-12 flex-none rounded-full bg-gray-50"
              :src="post.user.imageUrl"
              alt=""
            />
            <div class="min-w-0 flex-auto">
              <p class="text-sm font-semibold leading-6 text-gray-900">
                {{ post.user.name }}
              </p>
              <p class="mt-1 truncate text-xs leading-5 text-gray-500">
                {{ post.user.email }}
              </p>
            </div>
          </div>
        </li>
      </ul>
    </div>
  </AuthenticatedLayout>
</template>
