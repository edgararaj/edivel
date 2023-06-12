<script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { useForm } from "@inertiajs/vue3";
import Modal from "@/Components/Modal.vue";
import { ref } from "vue";

const bodyInput = ref(null);

const form = useForm({
  body: "",
});

const createPost = () => {
  form.post(route("post.create"), {
    preserveScroll: true,
    onSuccess: () => form.reset(),
    onError: () => {
      if (form.errors.body) {
        form.reset("body");
        body.value.focus();
      }
    },
  });
};
</script>

<template>
  <Modal>
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
          <form @submit.prevent="createPost" class="space-y-6">
            <div>
              <InputLabel for="body" value="Body" />

              <TextInput
                id="body"
                ref="bodyInput"
                v-model="form.body"
                type="password"
                class="mt-1 block w-full"
                autocomplete="body"
              />

              <InputError :message="form.errors.body" class="mt-2" />
            </div>

            <div class="flex items-center gap-4">
              <PrimaryButton :disabled="form.processing">Save</PrimaryButton>

              <Transition
                enter-from-class="opacity-0"
                leave-to-class="opacity-0"
                class="transition ease-in-out"
              >
                <p
                  v-if="form.recentlySuccessful"
                  class="text-sm text-gray-600 dark:text-gray-400"
                >
                  Saved.
                </p>
              </Transition>
            </div>
          </form>
        </div>
      </div>
    </div>
  </Modal>
</template>
