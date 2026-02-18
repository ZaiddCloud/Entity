<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';

const output = ref('');
const isRunning = ref(false);
const activeTab = ref('import'); // import | sync | seed

// Forms
const importForm = ref({
    path: '/home/z/PhpstormProjects/Entity/storage/app/transcripts'
});

const syncForm = ref({
    path: '' 
});

const manuscriptForm = ref({
    path: '/home/z/PhpstormProjects/Entity/storage/app/manuscripts'
});

const dataSyncForm = ref({
    path: '',
    source: '',
    dryRun: true
});

const runCommand = async (command, args = {}) => {
    isRunning.value = true;
    output.value = 'Running command...\n';
    
    try {
        const response = await axios.post(route('api.system.run-command'), {
            command,
            args
        });
        
        output.value += (response.data.output || '');
        output.value += '\n[SUCCESS] Command finished.';
    } catch (error) {
        output.value += '\n[ERROR] ' + (error.response?.data?.message || error.message);
        if (error.response?.data?.output) {
            output.value += '\n' + error.response.data.output;
        }
    } finally {
        isRunning.value = false;
    }
};

const handleImport = () => {
    runCommand('media:import-transcripts', { path: importForm.value.path });
};

const handleSync = () => {
    runCommand('storage:sync', { path: syncForm.value.path });
};

const handleManuscriptSync = () => {
    runCommand('manuscript:sync', { path: manuscriptForm.value.path });
};

const handleDataSync = () => {
    if (!dataSyncForm.value.path) {
        output.value = '[ERROR] الرجاء اختيار ملف أولاً من خلال زر "تصفح"\n';
        return;
    }
    
    const args = { file: dataSyncForm.value.path };
    
    if (dataSyncForm.value.source) args['--source'] = dataSyncForm.value.source;
    if (dataSyncForm.value.dryRun) args['--dry-run'] = true;
    
    runCommand('manuscriptsData:sync', args);
};

// File Browser Logic
const showBrowser = ref(false);
const browserPath = ref('');
const browserItems = ref([]);
const browserLoading = ref(false);

const loadFiles = async (path = '') => {
    browserLoading.value = true;
    try {
        const response = await axios.post(route('api.system.list-files'), { path });
        browserItems.value = response.data.items;
        browserPath.value = response.data.current_path;
    } catch (error) {
        console.error("Failed to list files", error);
    } finally {
        browserLoading.value = false;
    }
};

const navigateBrowser = (item) => {
    if (item.type === 'folder') {
        loadFiles(item.path);
    } else {
        // Select file: item.path is now absolute from listFiles
        dataSyncForm.value.path = item.path;
        showBrowser.value = false;
    }
};

const navigateUp = () => {
    // listFiles returns parent_path in response, or we can calculate dirname
    // But listFiles handles '..' correctly if we pass the folder.
    // If we are at root, maybe we can't go up.
    // Let's rely on the API to handle '..' or parent_path if provided.
    // Current implementation of listFiles returns '..' as a folder item.
    // So navigateBrowser will handle it.
    // BUT we also have the "Up" button.
    const parent = browserPath.value.split('/').slice(0, -1).join('/') || '/';
    loadFiles(parent); 
};

const openBrowser = () => {
    showBrowser.value = true;
    loadFiles(browserPath.value || '');
};

</script>

<template>
    <Head title="System Commands" />
    
    <div class="min-h-screen bg-gray-950 font-sans text-gray-300 p-8" dir="rtl">
        <div class="max-w-4xl mx-auto">
            
            <header class="flex items-center justify-between mb-8 pb-6 border-b border-gray-800">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-primary-600 rounded-xl flex items-center justify-center text-white text-xl">
                        ⚡
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-white">لوحة تحكم الأوامر</h1>
                        <p class="text-gray-500">تشغيل أوامر النظام والمزامنة</p>
                    </div>
                </div>
                
                <a href="/dashboard" class="px-4 py-2 bg-gray-800 hover:bg-gray-700 rounded-lg transition-colors text-sm">
                    العودة للرئيسية
                </a>
            </header>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Left: Sidebar / Command List -->
                <div class="lg:col-span-1 space-y-2">
                    <button 
                        @click="activeTab = 'import'"
                        class="w-full text-right p-4 rounded-xl border transition-all flex items-center gap-3"
                        :class="activeTab === 'import' ? 'bg-primary-900/20 border-primary-500/50 text-white' : 'bg-gray-900 border-gray-800 hover:border-gray-700'"
                    >
                        <span class="text-2xl">📄</span>
                        <div>
                            <div class="font-bold">استيراد التفريغات</div>
                            <div class="text-xs opacity-60">media:import-transcripts</div>
                        </div>
                    </button>

                    <button 
                        @click="activeTab = 'sync'"
                        class="w-full text-right p-4 rounded-xl border transition-all flex items-center gap-3"
                        :class="activeTab === 'sync' ? 'bg-blue-900/20 border-blue-500/50 text-white' : 'bg-gray-900 border-gray-800 hover:border-gray-700'"
                    >
                        <span class="text-2xl">🔄</span>
                        <div>
                            <div class="font-bold">مزامنة الملفات</div>
                            <div class="text-xs opacity-60">storage:sync</div>
                        </div>
                    </button>
                    
                    <button 
                        @click="activeTab = 'seed'"
                        class="w-full text-right p-4 rounded-xl border transition-all flex items-center gap-3"
                        :class="activeTab === 'seed' ? 'bg-green-900/20 border-green-500/50 text-white' : 'bg-gray-900 border-gray-800 hover:border-gray-700'"
                    >
                        <span class="text-2xl">🌱</span>
                        <div>
                            <div class="font-bold">توليد بيانات</div>
                            <div class="text-xs opacity-60">project:seed-realistic</div>
                        </div>
                    </button>

                    <button 
                        @click="activeTab = 'manuscript'"
                        class="w-full text-right p-4 rounded-xl border transition-all flex items-center gap-3"
                        :class="activeTab === 'manuscript' ? 'bg-amber-900/20 border-amber-500/50 text-white' : 'bg-gray-900 border-gray-800 hover:border-gray-700'"
                    >
                        <span class="text-2xl">📜</span>
                        <div>
                            <div class="font-bold">مزامنة المخطوطات</div>
                            <div class="text-xs opacity-60">manuscript:sync</div>
                        </div>
                    </button>

                    <button 
                        dusk="manuscriptsData-sync-section"
                        @click="activeTab = 'datasync'"
                        class="w-full text-right p-4 rounded-xl border transition-all flex items-center gap-3"
                        :class="activeTab === 'datasync' ? 'bg-purple-900/20 border-purple-500/50 text-white' : 'bg-gray-900 border-gray-800 hover:border-gray-700'"
                    >
                        <span class="text-2xl">📊</span>
                        <div>
                            <div class="font-bold">استيراد بيانات (Legacy)</div>
                            <div class="text-xs opacity-60">manuscriptsData:sync</div>
                        </div>
                    </button>

                    <button 
                        @click="activeTab = 'clear'"
                        class="w-full text-right p-4 rounded-xl border transition-all flex items-center gap-3"
                        :class="activeTab === 'clear' ? 'bg-red-900/20 border-red-500/50 text-white' : 'bg-gray-900 border-gray-800 hover:border-gray-700'"
                    >
                        <span class="text-2xl">🧹</span>
                        <div>
                            <div class="font-bold">تنظيف الكاش</div>
                            <div class="text-xs opacity-60">optimize:clear</div>
                        </div>
                    </button>
                </div>

                <!-- Right: Content & Output -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Command Config Panel -->
                    <div class="bg-gray-900 border border-gray-800 rounded-xl p-6">
                        
                        <!-- IMPORT TAB -->
                        <div v-if="activeTab === 'import'">
                            <h2 class="text-xl font-bold text-white mb-4">استيراد التفريغات النصية</h2>
                            <p class="text-gray-400 text-sm mb-6 leading-relaxed">
                                يقوم هذا الأمر بقراءة ملفات WORD (.docx) واستخراج المقاطع الزمنية منها بناءً على النمط المحدد، ثم ربطها بملفات الصوت/الفيديو الموجودة.
                            </p>
                            
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-300 mb-2">مسار الملفات (مجلد أو ملف)</label>
                                <div class="flex gap-2">
                                    <input 
                                        v-model="importForm.path" 
                                        type="text" 
                                        class="flex-1 bg-gray-950 border border-gray-700 rounded-lg px-4 py-2 text-white font-mono text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent dir-ltr"
                                        placeholder="/path/to/transcripts"
                                    >
                                </div>
                                <p class="text-xs text-gray-500 mt-2">يقبل المسار الكامل لمجلد يحتوي على ملفات docx، أو مسار لملف واحد.</p>
                            </div>

                            <button 
                                @click="handleImport" 
                                :disabled="isRunning"
                                class="w-full py-3 bg-primary-600 hover:bg-primary-500 text-white font-bold rounded-lg transition-colors flex items-center justify-center gap-2"
                            >
                                <span v-if="isRunning" class="animate-spin">⏳</span>
                                <span>تنفيذ الاستيراد</span>
                            </button>
                        </div>

                         <!-- SYNC TAB -->
                         <div v-if="activeTab === 'sync'">
                             <h2 class="text-xl font-bold text-white mb-4">مزامنة ملفات التخزين</h2>
                             <p class="text-gray-400 text-sm mb-6 leading-relaxed">
                                 فحص مجلد التخزين (storage/app/public) أو مجلد خارجي، وتسجيل الملفات الجديدة في قاعدة البيانات تلقائياً.
                                 <br><span class="text-yellow-500/80">ملاحظة: للمجلدات الخارجية، سيتم إنشاء رابط (Symlink) تلقائياً لتتمكن من تشغيل الملفات.</span>
                             </p>
                             
                             <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-300 mb-2">مسار خارجي (اختياري)</label>
                                <div class="flex gap-2">
                                    <input 
                                        v-model="syncForm.path" 
                                        type="text" 
                                        class="flex-1 bg-gray-950 border border-gray-700 rounded-lg px-4 py-2 text-white font-mono text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent dir-ltr"
                                        placeholder="/path/to/external/drive (leave empty for internal storage)"
                                    >
                                </div>
                            </div>

                             <button 
                                 @click="handleSync" 
                                 :disabled="isRunning"
                                 class="w-full py-3 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-lg transition-colors flex items-center justify-center gap-2"
                             >
                                 <span v-if="isRunning" class="animate-spin">⏳</span>
                                 <span>بدء المزامنة</span>
                             </button>
                         </div>

                         <!-- SEED TAB -->
                         <div v-if="activeTab === 'seed'">
                             <h2 class="text-xl font-bold text-white mb-4">توليد بيانات وهمية</h2>
                             <p class="text-gray-400 text-sm mb-6 leading-relaxed">
                                 مسح قاعدة البيانات الحالية وإعادة تعبئتها ببيانات تجريبية واقعية لأغراض الاختبار والتطوير.
                             </p>
                             <button 
                                 @click="runCommand('project:seed-realistic')" 
                                 :disabled="isRunning"
                                 class="w-full py-3 bg-green-600 hover:bg-green-500 text-white font-bold rounded-lg transition-colors flex items-center justify-center gap-2"
                             >
                                 <span v-if="isRunning" class="animate-spin">⏳</span>
                                 <span>توليد البيانات</span>
                             </button>
                         </div>

                         <!-- CLEAR TAB -->
                         <div v-if="activeTab === 'clear'">
                             <h2 class="text-xl font-bold text-white mb-4">تنظيف وتحسين الكاش</h2>
                             <p class="text-gray-400 text-sm mb-6 leading-relaxed">
                                 حذف ملفات الكاش، التكوين، والراوت. استخدم هذا إذا واجهت مشاكل غريبة أو تغييرات لا تظهر.
                             </p>
                             <button 
                                 @click="runCommand('optimize:clear')" 
                                 :disabled="isRunning"
                                 class="w-full py-3 bg-red-600 hover:bg-red-500 text-white font-bold rounded-lg transition-colors flex items-center justify-center gap-2"
                             >
                                 <span v-if="isRunning" class="animate-spin">⏳</span>
                                 <span>تنظيف النظام</span>
                             </button>
                         </div>

                         <!-- MANUSCRIPT SYNC TAB -->
                         <div v-if="activeTab === 'manuscript'">
                             <h2 class="text-xl font-bold text-white mb-4">مزامنة صفحات المخطوطات</h2>
                             <p class="text-gray-400 text-sm mb-6 leading-relaxed">
                                 يقوم هذا الأمر بقراءة ملفات WORD (.docx) واستخراج الصفحات منها بناءً على علامات الصفحات مثل [ص1] أو [صفحة 2]، ثم ربطها بالمخطوطات الموجودة.
                             </p>
                             
                             <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-300 mb-2">مسار الملفات (مجلد أو ملف)</label>
                                <div class="flex gap-2">
                                    <input 
                                        v-model="manuscriptForm.path" 
                                        type="text" 
                                        class="flex-1 bg-gray-950 border border-gray-700 rounded-lg px-4 py-2 text-white font-mono text-sm focus:ring-2 focus:ring-amber-500 focus:border-transparent dir-ltr"
                                        placeholder="/path/to/manuscripts"
                                    >
                                </div>
                                <p class="text-xs text-gray-500 mt-2">يقبل المسار الكامل لمجلد يحتوي على ملفات docx، أو مسار لملف واحد.</p>
                            </div>

                             <button 
                                 @click="handleManuscriptSync" 
                                 :disabled="isRunning"
                                 class="w-full py-3 bg-amber-600 hover:bg-amber-500 text-white font-bold rounded-lg transition-colors flex items-center justify-center gap-2"
                             >
                                 <span v-if="isRunning" class="animate-spin">⏳</span>
                                 <span>تنفيذ المزامنة</span>
                             </button>
                         </div>

                         <!-- DATA SYNC TAB (Legacy Import) -->
                         <div v-if="activeTab === 'datasync'">
                             <h2 class="text-xl font-bold text-white mb-4">استيراد بيانات المخطوطات (Legacy)</h2>
                             <p class="text-gray-400 text-sm mb-6 leading-relaxed">
                                 استيراد بيانات المخطوطات القديمة من ملفات CSV, Excel, JSON إلى قاعدة البيانات الجديدة.
                             </p>
                             
                             <div class="space-y-4 mb-6">
                                 <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">مسار الملف (CSV, XLSX, JSON)</label>
                                    <div class="flex gap-2">
                                        <input 
                                            dusk="file-path-input"
                                            v-model="dataSyncForm.path" 
                                            type="text" 
                                            class="flex-1 bg-gray-950 border border-gray-700 rounded-lg px-4 py-2 text-white font-mono text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent dir-ltr"
                                            placeholder="/path/to/data.csv"
                                        >
                                        <button 
                                            dusk="browse-button"
                                            @click="openBrowser"
                                            class="bg-gray-800 hover:bg-gray-700 border border-gray-700 text-gray-300 rounded-lg px-4 py-2 text-sm flex items-center gap-2 transition-colors"
                                        >
                                            <span>📂 تصفح</span>
                                        </button>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-2">اختر ملفاً من السيرفر أو اكتب المسار يدوياً.</p>
                                 </div>

                                 <!-- File Browser Modal -->
                                 <div dusk="file-browser-modal" v-if="showBrowser" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
                                     <div class="bg-gray-900 border border-gray-700 rounded-xl w-full max-w-2xl h-[500px] flex flex-col shadow-2xl">
                                         <!-- Header -->
                                         <div class="p-4 border-b border-gray-800 flex justify-between items-center bg-gray-950 rounded-t-xl">
                                             <h3 class="font-bold text-white flex items-center gap-2">
                                                 <span>📂</span>
                                                 <span class="dir-ltr text-sm font-mono text-gray-400 overflow-hidden text-ellipsis whitespace-nowrap max-w-md">{{ browserPath || 'Project Root' }}</span>
                                             </h3>
                                             <button dusk="close-browser-button" @click="showBrowser = false" class="text-gray-400 hover:text-white px-2">✕</button>
                                         </div>

                                         <!-- Browser Content -->
                                         <div class="flex-1 overflow-y-auto p-2">
                                             <div v-if="browserLoading" class="flex justify-center items-center h-full">
                                                 <span class="animate-spin text-2xl">⏳</span>
                                             </div>
                                             
                                             <div v-else class="space-y-1">
                                                 <!-- '..' is now returned by API if applicable, or we use button -->
                                                 
                                                 <button 
                                                     v-for="item in browserItems" 
                                                     :key="item.path"
                                                     @click="navigateBrowser(item)"
                                                     class="w-full text-right px-4 py-3 hover:bg-gray-800 rounded flex items-center gap-3 transition-colors border-b border-gray-800/50 last:border-0"
                                                 >
                                                     <span class="text-xl">{{ item.type === 'folder' ? '📁' : '📄' }}</span>
                                                     <div class="flex-1">
                                                         <div class="text-gray-200 font-medium dir-ltr text-right truncate">{{ item.name }}</div>
                                                     </div>
                                                     <span v-if="item.extension" class="text-xs text-gray-600 bg-gray-900 px-2 py-1 rounded uppercase">{{ item.extension }}</span>
                                                 </button>

                                                 <div v-if="browserItems.length === 0" class="text-center text-gray-500 py-8">
                                                     مجلد فارغ
                                                 </div>
                                             </div>
                                         </div>
                                     </div>
                                 </div>

                                 <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">المصدر (اختياري)</label>
                                    <input 
                                        dusk="source-input"
                                        v-model="dataSyncForm.source" 
                                        type="text" 
                                        class="w-full bg-gray-950 border border-gray-700 rounded-lg px-4 py-2 text-white text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                        placeholder="مثال: wam, makhtota"
                                    >
                                 </div>

                                 <div class="flex items-center gap-3 pt-2">
                                     <input 
                                        type="checkbox" 
                                        id="dryRun" 
                                        v-model="dataSyncForm.dryRun"
                                        dusk="dry-run-checkbox"
                                        class="w-4 h-4 rounded border-gray-700 bg-gray-900 text-purple-600 focus:ring-purple-500"
                                     >
                                     <label for="dryRun" class="text-sm text-gray-300 select-none cursor-pointer">تجربة فقط (Dry Run) - عدم الحفظ في قاعدة البيانات</label>
                                 </div>
                             </div>

                             <button 
                                 dusk="run-sync-command"
                                 @click="handleDataSync" 
                                 :disabled="isRunning"
                                 class="w-full py-3 bg-purple-600 hover:bg-purple-500 text-white font-bold rounded-lg transition-colors flex items-center justify-center gap-2"
                             >
                                 <span v-if="isRunning" class="animate-spin">⏳</span>
                                 <span>بدء الاستيراد</span>
                             </button>
                         </div>

                    </div>

                    <!-- Terminal Output -->
                    <div class="bg-black border border-gray-800 rounded-xl p-4 font-mono text-sm overflow-hidden h-80 flex flex-col">
                        <div class="flex items-center justify-between mb-2 pb-2 border-b border-gray-900">
                            <span class="text-gray-500 uppercase text-xs tracking-wider">Terminal Output</span>
                            <button @click="output = ''" class="text-xs text-gray-500 hover:text-white">Clear</button>
                        </div>
                        <pre class="flex-1 overflow-y-auto text-green-400 whitespace-pre-wrap p-2">{{ output || 'Ready...' }}</pre>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.dir-ltr { direction: ltr; }
</style>
