import Vue from 'vue'
import axios from 'axios'
import Routes from './routes'
import VueRouter from 'vue-router'
import Vuelidate from 'vuelidate'
import Echo from 'laravel-echo'

const io = require('socket.io-client')
const token = document.head.querySelector('meta[name="csrf-token"]')

if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content
}

Vue.prototype.$http = axios.create({
    baseURL: process.env.MIX_APP_URL + '/ajax/',
    timeout: 100000,
    headers: {
        'Access-Control-Allow-Origin': 'https://localhost',
        'Access-Control-Allow-Methods': 'GET, POST, PUT, DELETE, OPTIONS'
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
Vue.component('icon-check', require('./ui/icons/check').default)
Vue.component('card-gray', require('./ui/cards/gray').default)
Vue.component('card-white', require('./ui/cards/white').default)
Vue.component('card-elevated', require('./ui/cards/elevated').default)
Vue.component('logo-white', require('./ui/logos/white').default)
Vue.component('logo-default', require('./ui/logos/default').default)
Vue.component('container-white', require('./ui/containers/white').default)
Vue.component('alert-danger', require('./ui/alerts/danger').default)
Vue.component('alert-success', require('./ui/alerts/success').default)
Vue.component('modal', require('./ui/essentials/modal').default)
Vue.component('spinner', require('./ui/essentials/spinner').default)
Vue.component('list-sidebar', require('./ui/lists/sidebar').default)
Vue.component('illustration-hologram', require('./ui/illustrations/hologram').default)
Vue.component('icon-x', require('./ui/icons/x').default)
Vue.component('icon-server', require('./ui/icons/server').default)
Vue.component('icon-notification', require('./ui/icons/notification').default)
Vue.component('icon-refresh', require('./ui/icons/refresh').default)
Vue.component('icon-cheveron-right', require('./ui/icons/cheveron/right').default)

Vue.component('register-form', require('./views/auth/register/form').default)
Vue.component('password-form', require('./views/auth/passwords/form').default)

const router = new VueRouter({
    routes: Routes,
    mode: 'history',
    base: '/'
})

const vm = new Vue({
    components: {
        // navbar: require('./components/common/navbar').default,
        // sidebar: require('./components/common/sidebar').default
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
        this.$http.get('/bar')
            .then(function (response) {
                console.log(response.data);
                console.log(response.status);
                console.log(response.statusText);
                console.log(response.headers);
                console.log(response.config);
            });
    },
    methods: {
        foo() {
            return 'foo & bar'
        }
    }
})

vm.$mount('#app')
