import Vue from 'vue'
import axios from 'axios'
import Routes from './routes'
import VueRouter from 'vue-router'
import Vuelidate from 'vuelidate'
import Echo from 'laravel-echo'

import { directive as away } from 'vue-clickaway'

Vue.directive('away', away)

const io = require('socket.io-client')

Vue.prototype.$http = axios.create({
    baseURL: process.env.MIX_APP_URL + '/ajax/',
    headers: {
    }
});

Vue.prototype.$socket = new Echo({
    client: io,
    broadcaster: 'socket.io',
    host: process.env.MIX_SOCKET_HOST
});

Vue.use(VueRouter)
Vue.use(Vuelidate)

Vue.component('csrf', require('./essentials/csrf').default)
Vue.component('stripe', require('./essentials/stripe').default)
Vue.component('form-input', require('./ui/forms/input').default)
Vue.component('form-checkbox', require('./ui/forms/checkbox').default)
Vue.component('form-select', require('./ui/forms/select').default)
Vue.component('button-primary', require('./ui/buttons/primary').default)
Vue.component('button-github', require('./ui/buttons/github').default)
Vue.component('button-disabled', require('./ui/buttons/disabled').default)
Vue.component('heading-form', require('./ui/headings/form').default)
Vue.component('heading-card', require('./ui/headings/card').default)
Vue.component('divider-form', require('./ui/dividers/form').default)
Vue.component('divider-sidebar', require('./ui/dividers/sidebar').default)
Vue.component('card-gray', require('./ui/cards/gray').default)
Vue.component('card-white', require('./ui/cards/white').default)
Vue.component('card-elevated', require('./ui/cards/elevated').default)
Vue.component('logo-white', require('./ui/logos/white').default)
Vue.component('logo-default', require('./ui/logos/default').default)
Vue.component('container-white', require('./ui/containers/white').default)
Vue.component('alert-danger', require('./ui/alerts/danger').default)
Vue.component('alert-success', require('./ui/alerts/success').default)
Vue.component('modal', require('./ui/essentials/modal').default)
Vue.component('bar', require('./ui/essentials/bar').default)
Vue.component('spinner', require('./ui/essentials/spinner').default)
Vue.component('illustration-hologram', require('./ui/illustrations/hologram').default)
Vue.component('icon-x', require('./ui/icons/x').default)
Vue.component('icon-server', require('./ui/icons/server').default)
Vue.component('icon-notification', require('./ui/icons/notification').default)
Vue.component('icon-refresh', require('./ui/icons/refresh').default)
Vue.component('icon-cheveron-right', require('./ui/icons/cheveron/right').default)
Vue.component('icon-check', require('./ui/icons/check').default)

Vue.component('icon-home', require('./ui/icons/home').default)
Vue.component('icon-team', require('./ui/icons/team').default)
Vue.component('icon-folder', require('./ui/icons/folder').default)
Vue.component('icon-calendar', require('./ui/icons/calendar').default)
Vue.component('icon-inbox', require('./ui/icons/inbox').default)
Vue.component('icon-report', require('./ui/icons/report').default)

Vue.component('register-form', require('./views/auth/register/form').default)
Vue.component('password-form', require('./views/auth/passwords/form').default)

const router = new VueRouter({
    routes: Routes,
    mode: 'history',
    base: '/'
})

const vm = new Vue({
    components: {
        sidebar: require('./views/common/sidebar').default,
        navbar: require('./views/common/navbar').default
    },
    router,
    data() {
        return {
            old: {
            },
            erros: {
            }
        }
    },
    mounted() {
        document.getElementById('main').focus();

        this.$router.beforeEach((to, from, next) => {
            this.$root.$refs.bar.animate(0.4, [], next)
        });

        this.$router.afterEach(() => {
            this.$root.$refs.bar.animate(0.7)
        });
    },
    methods: {
        animate() {
            // this.$refs.spinner.show();
        }
    }
})

vm.$mount('#app')
