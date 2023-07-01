<script setup>

const props = defineProps(['modelValue'])
const emits = defineEmits(['update:modelValue', 'remove'])

const quantityAdd = (value) => {
  if (props.modelValue + value >= 1)
  {
    emits('update:modelValue', props.modelValue + value)
  }
  else
  {
    emits('remove')
  }
}

</script>
<template>
  <div class="border flex justify-between rounded">
    <button class="px-2 py-1 w-1/2" @click="quantityAdd(-1)">-</button>
    <input class="block w-10 text-center bg-inherit" readonly v-model="props.modelValue">
    <button class="px-2 py-1 w-1/2" @click="quantityAdd(1)">+</button>
  </div>
</template>
