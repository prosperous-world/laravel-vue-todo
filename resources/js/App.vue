<script setup>
import { ref, onMounted } from 'vue';
import { axios } from './bootstrap';
import TodoApp from './components/TodoApp.vue';

const user = ref(null);
const token = ref(localStorage.getItem('token') || '');
const loading = ref(false);
const mode = ref('login');

const authForm = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const errors = ref(null);

async function submitAuth() {
    loading.value = true;
    errors.value = null;
    try {
        const endpoint = mode.value === 'login' ? '/login' : '/register';
        const payload = mode.value === 'login'
            ? {
                email: authForm.value.email,
                password: authForm.value.password,
            }
            : authForm.value;

        const { data } = await axios.post(endpoint, payload);

        token.value = data.token;
        user.value = data.user;

        localStorage.setItem('token', token.value);
        axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;
    } catch (e) {
        errors.value = e.response?.data ?? { message: 'Authentication failed' };
    } finally {
        loading.value = false;
    }
}

async function checkMe() {
    if (!token.value) return;

    try {
        const { data } = await axios.get('/me');
        user.value = data;
    } catch {
        token.value = '';
        user.value = null;
        localStorage.removeItem('token');
        delete axios.defaults.headers.common['Authorization'];
    }
}

async function logout() {
    await axios.post('/logout').catch(() => {});
    token.value = '';
    user.value = null;
    localStorage.removeItem('token');
    delete axios.defaults.headers.common['Authorization'];
}

onMounted(() => {
    checkMe();
});
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-indigo-50 to-purple-50">
        <!-- Navbar -->
        <nav class="bg-white shadow-md">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Left: Todo App Title -->
                    <div class="flex items-center">
                        <h1 class="text-2xl font-bold text-indigo-600">Todo App</h1>
                    </div>

                    <!-- Right: User Info & Logout -->
                    <div v-if="user" class="flex items-center space-x-4">
                        <div class="text-right">
                            <p class="text-sm font-semibold text-gray-800">{{ user.name }}</p>
                            <p class="text-xs text-gray-500">{{ user.email }}</p>
                        </div>
                        <button
                            @click="logout"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 transition-colors"
                        >
                            Logout
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div v-if="!user" class="max-w-md mx-auto">
                <div class="bg-white shadow-xl rounded-xl p-6 md:p-8">
                    <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">Welcome to Todo App</h2>
                    <p class="text-sm text-gray-600 mb-6 text-center">Please login or register to manage your tasks</p>

                    <div class="flex space-x-2 mb-6">
                        <button
                            class="flex-1 py-2 rounded-md text-sm font-medium transition-colors"
                            :class="mode === 'login' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                            @click="mode = 'login'"
                        >
                            Login
                        </button>
                        <button
                            class="flex-1 py-2 rounded-md text-sm font-medium transition-colors"
                            :class="mode === 'register' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                            @click="mode = 'register'"
                        >
                            Register
                        </button>
                    </div>

                    <form class="space-y-4" @submit.prevent="submitAuth">
                        <div v-if="mode === 'register'">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                            <input
                                v-model="authForm.name"
                                type="text"
                                class="w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                required
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input
                                v-model="authForm.email"
                                type="email"
                                class="w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                required
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                            <input
                                v-model="authForm.password"
                                type="password"
                                class="w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                required
                            />
                        </div>

                        <div v-if="mode === 'register'">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                            <input
                                v-model="authForm.password_confirmation"
                                type="password"
                                class="w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                required
                            />
                        </div>

                        <div v-if="errors" class="text-xs text-red-600 space-y-1 bg-red-50 p-3 rounded">
                            <div v-if="errors.message">{{ errors.message }}</div>
                            <div v-for="(msgs, field) in errors.errors || {}" :key="field">
                                <span class="font-semibold">{{ field }}:</span> {{ msgs.join(', ') }}
                            </div>
                        </div>

                        <button
                            type="submit"
                            class="w-full flex justify-center items-center py-2.5 text-sm font-semibold rounded-md text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 transition-colors"
                            :disabled="loading"
                        >
                            <span v-if="!loading">{{ mode === 'login' ? 'Login' : 'Create account' }}</span>
                            <span v-else>Processing...</span>
                        </button>
                    </form>
                </div>
            </div>

            <div v-else>
                <TodoApp />
            </div>
        </div>
    </div>
</template>

