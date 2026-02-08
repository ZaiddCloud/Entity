<script setup>
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import db from '@/Core/Database/dexieApp';
import Card from '@/Components/Card.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    assignments: Object,
    users: Array,
    entities: Array
});

const localAssignments = ref([]);

onMounted(async () => {
    try {
        const allLocal = await db.entities.toArray();
        localAssignments.value = allLocal.sort((a, b) => 
            new Date(b.cached_at || b.updated_at) - new Date(a.cached_at || a.updated_at)
        );
        console.log('📡 Assignments: Loaded local entities from Dexie', localAssignments.value.length);
    } catch (e) {
        console.error('Failed to load local entities', e);
    }
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
            <div class="mb-8 border-b border-gray-100 dark:border-white/5 pb-8">
                <div class="flex flex-col md:flex-row md:justify-between md:items-end gap-6">
                    <div>
                        <Link 
                            :href="route('dashboard')" 
                            class="inline-flex items-center gap-1.5 text-xs font-bold text-gray-400 hover:text-indigo-600 transition-colors mb-3 group"
                        >
                            <i class="ri-arrow-right-line group-hover:translate-x-1 transition-transform"></i>
                            العودة للوحة القيادة
                        </Link>
                        
                        <div class="flex items-center gap-3 mb-1">
                            <h1 class="text-4xl font-black text-gray-900 dark:text-white tracking-tight">إدارة المهام</h1>
                            <span class="text-[9px] bg-indigo-50 dark:bg-indigo-500/10 text-indigo-500 px-3 py-1 rounded-full border border-indigo-100 dark:border-indigo-500/20 font-black uppercase tracking-[0.2em] flex items-center gap-2">
                                <i class="ri-wifi-line animate-pulse"></i>
                                Offline Mode
                            </span>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400 font-medium text-sm">تخصيص وتتبع الأذونات المتقدمة للأوفلاين</p>
                    </div>

                    <button 
                        @click="showAssignModal = true" 
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3.5 rounded-2xl font-black transition-all flex items-center gap-2 shadow-xl shadow-indigo-500/20 active:scale-95 text-sm"
                    >
                        <i class="ri-add-line text-lg"></i>
                        إسناد مهمة جديدة
                    </button>
                </div>
            </div>

            <!-- Offline Tasks Section (The Local Vault) -->
            <transition 
                enter-active-class="transform transition ease-out duration-500"
                enter-from-class="translate-y-4 opacity-0"
                enter-to-class="translate-y-0 opacity-100"
            >
                <div v-if="localAssignments.length > 0" class="mb-10">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-2 h-6 bg-lime-400 rounded-full" />
                        <h2 class="text-xl font-black text-gray-900 dark:text-white">مهامي المحملة (Offline Vault)</h2>
                        <span class="text-[10px] bg-lime-400/10 text-lime-600 px-2 py-0.5 rounded-lg font-black uppercase tracking-widest border border-lime-400/20">
                            جاهزة للعمل 📡
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <Link
                            v-for="item in localAssignments"
                            :key="item.id"
                            :href="item.slug ? route('reader.show', { type: item.type, slug: item.slug }) : '#'"
                            class="group bg-white dark:bg-[#0a0a0a] border border-gray-100 dark:border-white/5 p-5 rounded-[2rem] shadow-sm hover:shadow-2xl transition-all duration-500 hover:border-indigo-400"
                        >
                            <div class="flex items-start gap-4">
                                <div class="w-14 h-14 bg-gray-50 dark:bg-gray-800 rounded-2xl flex items-center justify-center text-2xl group-hover:rotate-6 transition-transform">
                                    {{ item.type === 'book' ? '📚' : item.type === 'audio' ? '🎵' : item.type === 'video' ? '🎬' : '📜' }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-black text-sm text-gray-900 dark:text-white truncate group-hover:text-indigo-500 transition-colors">
                                        {{ item.title }}
                                    </h4>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-[9px] font-black uppercase text-indigo-500 bg-indigo-50 dark:bg-indigo-500/10 px-2 py-0.5 rounded-lg">
                                            {{ item.type }}
                                        </span>
                                        <span v-if="item.sync_status === 'synced'" class="text-[9px] font-black text-green-500 flex items-center gap-1">
                                            <i class="ri-checkbox-circle-fill"></i> محدث
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>
            </transition>

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
