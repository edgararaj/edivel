
<script setup>
import { TransitionRoot, TransitionChild, Dialog, DialogPanel, DialogTitle } from "@headlessui/vue"
import { useModal } from "/vendor/emargareten/inertia-modal"

const { show, close, redirect } = useModal()
</script>

<template>
  <TransitionRoot appear as="template" :show="show">
    <Dialog as="div" class="relative z-10" @close="close">
      <TransitionChild @after-leave="redirect" as="template">
        <div class="fixed inset-0 bg-black/75 transition-opacity" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center">
          <TransitionChild as="template">
            <DialogPanel class="w-full max-w-lg transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all">
              <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900">
                <h1>Ola sou o paulo</h1>
              </DialogTitle>
              <p>ola</p>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>
