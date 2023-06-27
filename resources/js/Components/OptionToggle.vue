<script setup>
import { onMounted } from 'vue';
import {ref, watch} from 'vue'

const props = defineProps({
  values: Array,
  allValues : Array,
  updateState: Boolean,
  selected: {}
})

const emit = defineEmits(['change'])

watch(props, (v) => {
  if (props.updateState)
  {
    selected.value = v.selected
  }
})

const selected = ref(props.selected);

const changeSelectVal = (val) => {
  emit('change', val)
  selected.value = val;
}

</script>
<template>
  <div class="btn-group">
    <button type="button" v-for="key in allValues" @click="changeSelectVal(key)"
      :class="['btn mr-2 px-3', { 'bg-gray-300 rounded-xl': selected === key, 'text-gray-400': !values.includes(key) }]">{{ key }}</button>
  </div>
</template>
