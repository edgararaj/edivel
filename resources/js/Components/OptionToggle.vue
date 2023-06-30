<script setup>
import { onMounted } from 'vue'
import { ref, watch } from 'vue'
import stc from 'string-to-color'
import { contrastColor } from 'contrast-color'

const props = defineProps({
  values: Array,
  allValues: Array,
  updateState: Boolean,
  colorOption: Boolean,
  selected: {}
})

const emit = defineEmits(['change'])

function shadeBlendConvert(percent, from, to) {
  if (
    typeof percent != 'number' ||
    percent < -1 ||
    percent > 1 ||
    typeof from != 'string' ||
    (from[0] != 'r' && from[0] != '#') ||
    (to && typeof to != 'string')
  )
    return null; //ErrorCheck

  function pSBCr(d) {
    var l = d.length,
      RGB = {};
    if (l > 9) {
      d = d.split(',');
      if (d.length < 3 || d.length > 4) return null; //ErrorCheck
      (RGB[0] = i(d[0].split('(')[1])),
        (RGB[1] = i(d[1])),
        (RGB[2] = i(d[2])),
        (RGB[3] = d[3] ? parseFloat(d[3]) : -1);
    } else {
      if (l == 8 || l == 6 || l < 4) return null; //ErrorCheck
      if (l < 6)
        d =
          '#' +
          d[1] +
          d[1] +
          d[2] +
          d[2] +
          d[3] +
          d[3] +
          (l > 4 ? d[4] + '' + d[4] : ''); //3 or 4 digit
      (d = i(d.slice(1), 16)),
        (RGB[0] = (d >> 16) & 255),
        (RGB[1] = (d >> 8) & 255),
        (RGB[2] = d & 255),
        (RGB[3] = -1);
      if (l == 9 || l == 5)
        (RGB[3] = r(RGB[2] / 255 * 10000) / 10000),
          (RGB[2] = RGB[1]),
          (RGB[1] = RGB[0]),
          (RGB[0] = (d >> 24) & 255);
    }
    return RGB;
  };

  var i = parseInt,
    r = Math.round,
    h = from.length > 9,
    h =
      typeof to == 'string'
        ? to.length > 9 ? true : to == 'c' ? !h : false
        : h,
    b = percent < 0,
    percent = b ? percent * -1 : percent,
    to = to && to != 'c' ? to : b ? '#000' : '#FFF',
    f = pSBCr(from),
    t = pSBCr(to);
  if (!f || !t) return null; //ErrorCheck
  if (h)
    return (
      'rgb' +
      (f[3] > -1 || t[3] > -1 ? 'a(' : '(') +
      r((t[0] - f[0]) * percent + f[0]) +
      ',' +
      r((t[1] - f[1]) * percent + f[1]) +
      ',' +
      r((t[2] - f[2]) * percent + f[2]) +
      (f[3] < 0 && t[3] < 0
        ? ')'
        : ',' +
        (f[3] > -1 && t[3] > -1
          ? r(((t[3] - f[3]) * percent + f[3]) * 10000) / 10000
          : t[3] < 0 ? f[3] : t[3]) +
        ')')
    );
  else
    return (
      '#' +
      (
        0x100000000 +
        r((t[0] - f[0]) * percent + f[0]) * 0x1000000 +
        r((t[1] - f[1]) * percent + f[1]) * 0x10000 +
        r((t[2] - f[2]) * percent + f[2]) * 0x100 +
        (f[3] > -1 && t[3] > -1
          ? r(((t[3] - f[3]) * percent + f[3]) * 255)
          : t[3] > -1 ? r(t[3] * 255) : f[3] > -1 ? r(f[3] * 255) : 255)
      )
        .toString(16)
        .slice(1, f[3] > -1 || t[3] > -1 ? undefined : -2)
    );
}

const parseColor = (str, main) => {
  let color = stc(str)
  if (main) {
    return color
  }
  else {
    return shadeBlendConvert(0.9, color, '#fff')
  }
}

function padZero(str, len) {
  len = len || 2;
  var zeros = new Array(len).join('0');
  return (zeros + str).slice(-len);
}

function invertColor(hex, bw) {
  if (hex.indexOf('#') === 0) {
    hex = hex.slice(1);
  }
  // convert 3-digit hex to 6-digits.
  if (hex.length === 3) {
    hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
  }
  if (hex.length !== 6) {
    throw new Error('Invalid HEX color.');
  }
  var r = parseInt(hex.slice(0, 2), 16),
    g = parseInt(hex.slice(2, 4), 16),
    b = parseInt(hex.slice(4, 6), 16);
  if (bw) {
    // https://stackoverflow.com/a/3943023/112731
    return (r * 0.299 + g * 0.587 + b * 0.114) > 186
      ? '#000000'
      : '#FFFFFF';
  }
  // invert color components
  r = (255 - r).toString(16);
  g = (255 - g).toString(16);
  b = (255 - b).toString(16);
  // pad each with zeros and return
  return "#" + padZero(r) + padZero(g) + padZero(b);
}

const parseContrastColor = (str, main) => {
  return invertColor(parseColor(str, main), 1)
}

watch(props, (v) => {
  if (props.updateState) {
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
    <button v-if="colorOption" type="button" v-for="key in allValues" @click="changeSelectVal(key)"
      :class="['btn w-10 h-10 mr-1 mt-2 rounded border', { 'bg-indigo-500 text-white': selected === key, 'text-gray-400 border-0 line-through': !values.includes(key) }]"
      :style="{ 'background-color': parseColor(key, values.includes(key)), 'color': parseContrastColor(key, values.includes(key)) }">{{
        key === selected ? 'Y' : '' }}</button>
    <p v-if="colorOption">{{ selected }}</p>
    <button v-else="colorOption" type="button" v-for="key in allValues" @click="changeSelectVal(key)"
      :class="['btn mr-2 mt-2 px-4 py-1 rounded border', { 'bg-indigo-500 text-white': selected === key, 'text-gray-400 border-0 line-through': !values.includes(key) }]">{{ key}}</button>
  </div>
</template>
