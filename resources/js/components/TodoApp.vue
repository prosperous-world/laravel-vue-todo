<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { axios } from '../bootstrap';

const todos = ref([]);
const tags = ref([]);
const loading = ref(false);
const saving = ref(false);
const pagination = ref({
    current_page: 1,
    last_page: 1,
});

const filters = ref({
    search: '',
    status: 'all',
    tag: '',
    due_from: '',
    due_to: '',
    sort: 'due_date_asc',
});

const newTodo = ref({
    id: null,
    title: '',
    description: '',
    due_date: '',
    completed: false,
    tagsInput: '',
});

const formErrors = ref({});
const showModal = ref(false);

function resetForm() {
    newTodo.value = {
        id: null,
        title: '',
        description: '',
        due_date: '',
        completed: false,
        tagsInput: '',
    };
    formErrors.value = {};
    showModal.value = false;
}

function openNewTodoModal() {
    resetForm();
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    resetForm();
}

function parseTags(tagsInput) {
    return tagsInput
        .split(',')
        .map((t) => t.trim())
        .filter(Boolean);
}

function formatDate(dateString) {
    if (!dateString) return '';
    
    try {
        const date = new Date(dateString);
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const year = date.getFullYear();
        return `${month}/${day}/${year}`;
    } catch (error) {
        return dateString;
    }
}

function formatDateForInput(dateString) {
    if (!dateString) return '';
    
    try {
        const date = new Date(dateString);
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    } catch (error) {
        return dateString;
    }
}

async function fetchTodos(page = 1) {
    loading.value = true;
    try {
        const { data } = await axios.get('/todos', {
            params: {
                page,
                search: filters.value.search || undefined,
                status: filters.value.status,
                tag: filters.value.tag || undefined,
                due_from: filters.value.due_from || undefined,
                due_to: filters.value.due_to || undefined,
                sort: filters.value.sort,
            },
        });

        todos.value = data.data;
        pagination.value.current_page = data.current_page;
        pagination.value.last_page = data.last_page;
    } catch (error) {
        console.error('Failed to fetch todos:', error);
    } finally {
        loading.value = false;
    }
}

async function fetchTags() {
    try {
        const { data } = await axios.get('/tags');
        tags.value = data;
    } catch (error) {
        console.error('Failed to fetch tags:', error);
    }
}

function isEditing() {
    return !!newTodo.value.id;
}

function edit(todo) {
    newTodo.value = {
        id: todo.id,
        title: todo.title,
        description: todo.description || '',
        due_date: formatDateForInput(todo.due_date),
        completed: todo.completed,
        tagsInput: todo.tags.map((t) => t.name).join(', '),
    };
    formErrors.value = {};
    showModal.value = true;
}

async function saveTodo() {
    formErrors.value = {};

    if (!newTodo.value.title.trim()) {
        formErrors.value.title = ['Title is required'];
        return;
    }

    saving.value = true;
    try {
        const payload = {
            title: newTodo.value.title,
            description: newTodo.value.description || null,
            due_date: newTodo.value.due_date || null,
            completed: !!newTodo.value.completed,
            tags: parseTags(newTodo.value.tagsInput),
        };

        if (isEditing()) {
            await axios.put(`/todos/${newTodo.value.id}`, payload);
        } else {
            await axios.post('/todos', payload);
        }

        await Promise.all([fetchTodos(pagination.value.current_page), fetchTags()]);
        closeModal();
    } catch (e) {
        if (e.response?.data?.errors) {
            formErrors.value = e.response.data.errors;
        }
    } finally {
        saving.value = false;
    }
}

async function deleteTodo(todo) {
    if (!confirm('Are you sure you want to delete this todo?')) return;

    try {
        await axios.delete(`/todos/${todo.id}`);
        await Promise.all([fetchTodos(pagination.value.current_page), fetchTags()]);
    } catch (error) {
        console.error('Failed to delete todo:', error);
        alert('Failed to delete todo. Please try again.');
    }
}

async function toggle(todo) {
    try {
        await axios.patch(`/todos/${todo.id}/toggle`);
        await fetchTodos(pagination.value.current_page);
    } catch (error) {
        console.error('Failed to toggle todo:', error);
    }
}

const hasActiveFilters = computed(() => {
    const { search, status, tag, due_from, due_to } = filters.value;
    return (
        !!search ||
        status !== 'all' ||
        !!tag ||
        !!due_from ||
        !!due_to
    );
});

function clearFilters() {
    filters.value = {
        search: '',
        status: 'all',
        tag: '',
        due_from: '',
        due_to: '',
        sort: 'due_date_asc',
    };
    fetchTodos(1);
}

watch(
    () => ({ ...filters.value }),
    () => {
        fetchTodos(1);
    },
    { deep: true }
);

onMounted(async () => {
    await Promise.all([fetchTodos(), fetchTags()]);
});
</script>

<template>
    <div class="space-y-6">
        <!-- Header with New Todo Button -->
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800">My Todos</h2>
            <button
                @click="openNewTodoModal"
                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors font-medium text-sm flex items-center gap-2"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                New Todo
            </button>
        </div>

        <!-- Horizontal Filters Section -->
        <div class="bg-white rounded-lg shadow-sm p-4 border">
            <div class="flex flex-wrap items-end gap-4">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Search</label>
                    <input
                        v-model="filters.search"
                        type="text"
                        class="w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Search title or description"
                    />
                </div>

                <div class="w-32">
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Status</label>
                    <select
                        v-model="filters.status"
                        class="w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="all">All</option>
                        <option value="completed">Completed</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>

                <div class="w-40">
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Tag</label>
                    <select
                        v-model="filters.tag"
                        class="w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="">All Tags</option>
                        <option v-for="tag in tags" :key="tag.id" :value="tag.slug">
                            {{ tag.name }}
                        </option>
                    </select>
                </div>

                <div class="w-36">
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Due From</label>
                    <input
                        v-model="filters.due_from"
                        type="date"
                        class="w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                </div>

                <div class="w-36">
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Due To</label>
                    <input
                        v-model="filters.due_to"
                        type="date"
                        class="w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                </div>

                <div class="w-48">
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Sort By</label>
                    <select
                        v-model="filters.sort"
                        class="w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="due_date_asc">Due date (earliest first)</option>
                        <option value="due_date_desc">Due date (latest first)</option>
                        <option value="created_at_desc">Newest created</option>
                    </select>
                </div>

                <button
                    v-if="hasActiveFilters"
                    type="button"
                    class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors"
                    @click="clearFilters"
                >
                    Clear Filters
                </button>
            </div>
        </div>

        <!-- Todos List -->
        <div class="bg-white rounded-lg shadow-sm border">
            <div class="p-4 border-b flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-800">Todos</h3>
                <div class="text-xs text-gray-500">
                    Page {{ pagination.current_page }} of {{ pagination.last_page }}
                </div>
            </div>

            <div v-if="loading" class="text-sm text-gray-500 py-12 text-center">Loading todos...</div>

            <div v-else-if="!todos.length" class="text-sm text-gray-500 py-12 text-center">
                <p class="mb-2">No todos found.</p>
                <p class="text-xs">Try adding one or adjusting your filters.</p>
            </div>

            <div v-else class="divide-y max-h-[600px] overflow-y-auto">
                <div
                    v-for="todo in todos"
                    :key="todo.id"
                    class="p-4 flex items-start gap-3 hover:bg-gray-50 transition-colors"
                >
                    <div class="pt-1">
                        <input
                            type="checkbox"
                            class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                            :checked="todo.completed"
                            @change="toggle(todo)"
                        />
                    </div>
                    <div class="flex-1">
                        <div class="flex justify-between gap-3">
                            <div>
                                <h3
                                    class="text-sm font-semibold"
                                    :class="todo.completed ? 'line-through text-gray-400' : 'text-gray-800'"
                                >
                                    {{ todo.title }}
                                </h3>
                                <p v-if="todo.description" class="text-xs text-gray-600 mt-1">
                                    {{ todo.description }}
                                </p>
                            </div>
                            <div class="text-right text-xs space-y-1">
                                <p v-if="todo.due_date" class="text-gray-600">
                                    Due: <span class="font-medium">{{ formatDate(todo.due_date) }}</span>
                                </p>
                                <p class="text-gray-400">
                                    {{ todo.completed ? 'Completed' : 'Pending' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex justify-between items-center mt-2">
                            <div class="flex flex-wrap gap-1">
                                <span
                                    v-for="tag in todo.tags"
                                    :key="tag.id"
                                    class="inline-flex items-center px-2 py-0.5 text-[10px] rounded-full bg-indigo-100 text-indigo-700 font-medium"
                                >
                                    {{ tag.name }}
                                </span>
                            </div>

                            <div class="flex gap-2 text-xs">
                                <button
                                    class="text-indigo-600 hover:underline font-medium"
                                    @click="edit(todo)"
                                >
                                    Edit
                                </button>
                                <button
                                    class="text-red-600 hover:underline font-medium"
                                    @click="deleteTodo(todo)"
                                >
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div
                v-if="pagination.last_page > 1"
                class="flex justify-end items-center gap-2 mt-4 text-xs"
            >
                <button
                    class="px-3 py-1 border rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    :disabled="pagination.current_page <= 1"
                    @click="fetchTodos(pagination.current_page - 1)"
                >
                    Prev
                </button>
                <button
                    class="px-3 py-1 border rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    :disabled="pagination.current_page >= pagination.last_page"
                    @click="fetchTodos(pagination.current_page + 1)"
                >
                    Next
                </button>
            </div>
        </div>

        <!-- Modal for Add/Edit Todo -->
        <div
            v-if="showModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
            @click.self="closeModal"
        >
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="sticky top-0 bg-white border-b px-6 py-4 flex justify-between items-center">
                    <h3 class="text-xl font-bold text-gray-800">
                        {{ isEditing() ? 'Edit Todo' : 'Add New Todo' }}
                    </h3>
                    <button
                        @click="closeModal"
                        class="text-gray-400 hover:text-gray-600 transition-colors"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="saveTodo" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Title *</label>
                        <input
                            v-model="newTodo.title"
                            type="text"
                            class="w-full border rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="What do you need to do?"
                        />
                        <p v-if="formErrors.title" class="text-xs text-red-600 mt-1">
                            {{ formErrors.title[0] }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Description</label>
                        <textarea
                            v-model="newTodo.description"
                            rows="3"
                            class="w-full border rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="Optional details..."
                        ></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Due Date</label>
                            <input
                                v-model="newTodo.due_date"
                                type="date"
                                class="w-full border rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                            <p v-if="formErrors.due_date" class="text-xs text-red-600 mt-1">
                                {{ formErrors.due_date[0] }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Tags (comma separated)</label>
                            <input
                                v-model="newTodo.tagsInput"
                                type="text"
                                class="w-full border rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                placeholder="work, personal, urgent"
                            />
                            <p v-if="formErrors['tags.0']" class="text-xs text-red-600 mt-1">
                                {{ formErrors['tags.0'][0] }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <input
                            id="completed"
                            v-model="newTodo.completed"
                            type="checkbox"
                            class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                        />
                        <label for="completed" class="text-sm text-gray-700">Mark as completed</label>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t">
                        <button
                            type="button"
                            @click="closeModal"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 text-sm font-semibold bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-60 transition-colors"
                            :disabled="saving"
                        >
                            {{ saving ? 'Saving...' : (isEditing() ? 'Update Todo' : 'Add Todo') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

