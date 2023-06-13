<script setup>
import {
  TransitionRoot,
  TransitionChild,
  Dialog,
  DialogPanel,
  DialogTitle,
} from "@headlessui/vue";
import { useModal } from "/vendor/emargareten/inertia-modal";
import { useForm } from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import Modal from "@/Components/Modal.vue";
import { ref } from "vue";

const { show, close, redirect } = useModal();

const form = useForm({
  body: "",
});

const createPost = () => {
  form.post(route("posts.store"), {
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
  <TransitionRoot appear as="template" :show="show">
    <Dialog as="div" class="relative z-10" @close="close">
      <TransitionChild @after-leave="redirect" as="template">
        <div class="fixed inset-0 bg-black/75 transition-opacity" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto">
        <div
          class="flex min-h-full items-center justify-center p-4 text-center"
        >
          <TransitionChild as="template">
            <DialogPanel
              class="w-full max-w-lg transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all"
            >
              <form @submit.prevent="createPost" class="space-y-6">
                <div>
                  <InputLabel for="body" value="Body" />

                  <TextInput
                    id="body"
                    ref="bodyInput"
                    v-model="form.body"
                    type="text"
                    class="mt-1 block w-full"
                    autocomplete="body"
                  />

                  <InputError :message="form.errors.body" class="mt-2" />
                </div>

                <div class="flex items-center gap-4">
                  <PrimaryButton :disabled="form.processing"
                    >Save</PrimaryButton
                  >

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
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>
