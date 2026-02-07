<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    assignments: Object,
    users: Array,
    entities: Array
});

const showAssignModal = ref(false);
const showEditModal = ref(false);
const selectedAssignment = ref(null);

const form = ref({
    user_id: '',
    entity_type: '',
    entity_id: '',
    notes: '',
    due_at: ''
});

const editForm = ref({
    status: '',
    notes: '',
    due_at: ''
});

// Filter entities by type for better UX
const entityTypes = computed(() => {
    const types = new Set(props.entities.map(e => e.type));
    return Array.from(types);
});

const filteredEntities = computed(() => {
    if (!form.value.entity_type) return props.entities;
    return props.entities.filter(e => e.type_class === form.value.entity_type);
});

const assignTask = () => {
    router.post('/assignments', form.value, {
        onSuccess: () => {
            showAssignModal.value = false;
            form.value = { user_id: '', entity_type: '', entity_id: '', notes: '', due_at: '' };
        }
    });
};

const openEditModal = (assignment) => {
    selectedAssignment.value = assignment;
    editForm.value = {
        status: assignment.status,
        notes: assignment.notes || '',
        due_at: assignment.due_at || ''
    };
    showEditModal.value = true;
};

const updateAssignment = () => {
    router.put(`/assignments/${selectedAssignment.value.id}`, editForm.value, {
        onSuccess: () => {
            showEditModal.value = false;
            selectedAssignment.value = null;
        }
    });
};

const revokeAssignment = (assignmentId) => {
    if (confirm('هل أنت متأكد من إلغاء هذا الإسناد؟')) {
        router.delete(`/assignments/${assignmentId}`);
    }
};

const getStatusBadgeClass = (status) => {
    const classes = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'in_progress': 'bg-blue-100 text-blue-800',
        'completed': 'bg-green-100 text-green-800',
        'cancelled': 'bg-red-100 text-red-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const getStatusLabel = (status) => {
    const labels = {
        'pending': 'قيد الانتظار',
        'in_progress': 'قيد التنفيذ',
        'completed': 'مكتمل',
        'cancelled': 'ملغي'
    };
    return labels[status] || status;
};
</script>

<template>
    <Head title="إدارة المهام" />

    <div class="min-h-screen bg-gray-50 p-6">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">إدارة المهام</h1>
                    <p class="text-gray-600 mt-1">إسناد وتتبع المهام للمستخدمين</p>
                </div>
                <button 
                    @click="showAssignModal = true" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors flex items-center gap-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    إسناد مهمة جديدة
                </button>
            </div>

            <!-- Assignments Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المستخدم</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الكيان</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">النوع</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحالة</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">تاريخ الاستحقاق</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">أُسند بواسطة</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="assignment in assignments.data" :key="assignment.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ assignment.user?.name }}</div>
                                <div class="text-sm text-gray-500">{{ assignment.user?.email }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">{{ assignment.entity?.title }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-600">{{ assignment.entity_type.split('\\').pop() }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="getStatusBadgeClass(assignment.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                    {{ getStatusLabel(assignment.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ assignment.due_at ? new Date(assignment.due_at).toLocaleDateString('ar-EG') : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ assignment.assigner?.name || '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button @click="openEditModal(assignment)" class="text-blue-600 hover:text-blue-900 ml-3">
                                    تعديل
                                </button>
                                <button @click="revokeAssignment(assignment.id)" class="text-red-600 hover:text-red-900">
                                    إلغاء
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div v-if="assignments.links" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-700">
                            عرض <span class="font-medium">{{ assignments.from }}</span> إلى <span class="font-medium">{{ assignments.to }}</span> من <span class="font-medium">{{ assignments.total }}</span> نتيجة
                        </div>
                        <div class="flex gap-2">
                            <a v-for="link in assignments.links" :key="link.label" 
                               :href="link.url" 
                               :class="[
                                   'px-3 py-2 text-sm rounded',
                                   link.active ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'
                               ]"
                               v-html="link.label"
                            ></a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assign Modal -->
            <div v-if="showAssignModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg p-6 w-full max-w-md">
                    <h2 class="text-xl font-bold mb-4">إسناد مهمة جديدة</h2>
                    <form @submit.prevent="assignTask">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">المستخدم</label>
                                <select v-model="form.user_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
                                    <option value="">اختر مستخدم</option>
                                    <option v-for="user in users" :key="user.id" :value="user.id">
                                        {{ user.name }} ({{ user.email }})
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">نوع الكيان</label>
                                <select v-model="form.entity_type" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
                                    <option value="">اختر نوع</option>
                                    <option v-for="type in entityTypes" :key="type" :value="entities.find(e => e.type === type).type_class">
                                        {{ type }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">الكيان</label>
                                <select v-model="form.entity_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2" :disabled="!form.entity_type">
                                    <option value="">اختر كيان</option>
                                    <option v-for="entity in filteredEntities" :key="entity.id" :value="entity.id">
                                        {{ entity.title }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">تاريخ الاستحقاق</label>
                                <input v-model="form.due_at" type="date" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">ملاحظات</label>
                                <textarea v-model="form.notes" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2"></textarea>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 mt-6">
                            <button type="button" @click="showAssignModal = false" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                                إلغاء
                            </button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                إسناد
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Edit Modal -->
            <div v-if="showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg p-6 w-full max-w-md">
                    <h2 class="text-xl font-bold mb-4">تعديل المهمة</h2>
                    <form @submit.prevent="updateAssignment">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">الحالة</label>
                                <select v-model="editForm.status" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
                                    <option value="pending">قيد الانتظار</option>
                                    <option value="in_progress">قيد التنفيذ</option>
                                    <option value="completed">مكتمل</option>
                                    <option value="cancelled">ملغي</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">تاريخ الاستحقاق</label>
                                <input v-model="editForm.due_at" type="date" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">ملاحظات</label>
                                <textarea v-model="editForm.notes" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2"></textarea>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 mt-6">
                            <button type="button" @click="showEditModal = false" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                                إلغاء
                            </button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                حفظ
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.modal {
    animation: fadeIn 0.2s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}
</style>
