import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '/vendor/tightenco/ziggy/dist/vue.m';
import { modal } from '/vendor/emargareten/inertia-modal'
import { createStore } from 'vuex';
import createMultiTabState from 'vuex-multi-tab-state';
import axios from 'axios';

// const channel = Echo.channel('public.playground');
// channel.subscribed(() => {
//   console.log("subscribed");
// }).listen(".playground", (event) => {
//   console.log(event);
// })

const store = createStore({
  plugins: [createMultiTabState()],
  state: {
    products: [],
    cart: [],
    order: {}
  },
  mutations: {
    updateProducts(state, products) {
      state.products = products;
    },
    addToCart(state, product) {
      let productInCartIndex = state.cart.findIndex(item => item.slug === product.slug);
      if (productInCartIndex !== -1) {
        state.cart[productInCartIndex].quantity++;
        return;
      }

      state.cart.push(product);
    },
    removeFromCart(state, index) {
      state.cart.splice(index, 1);
    },
    updateOrder(state, order) {
      state.order = order;
    },
    updateCart(state, cart) {
      state.cart = cart;
    }
  },
  actions: {
    clearCart({ commit }) {
      commit('updateCart', []);
    }
  },
  modules: {

  }
})

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel';

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
  data: {
    langs: ['JavaScript', 'PHP', 'HTML', 'CSS', 'Ruby', 'Python', 'Erlang'],
    paginate: ['languages']
  },
  setup({ el, App, props, plugin }) {
    return createApp({ render: () => h(App, props) })
      .use(modal, {
        resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
      })
      .use(store)
      .use(plugin)
      .use(ZiggyVue, Ziggy)
      .mount(el);
  },
  progress: {
    color: '#4B5563',
  },
});
