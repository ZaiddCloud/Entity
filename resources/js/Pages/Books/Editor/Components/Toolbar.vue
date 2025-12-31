<template>
    <div v-if="editor" class="editor-toolbar glass-effect p-1 flex items-center gap-1 overflow-x-auto no-scrollbar">
        <!-- Text Styles -->
        <div class="flex items-center gap-1 pr-1 border-l border-white/20 dark:border-white/10 ml-1">
            <ToolbarButton 
                @click="editor.chain().focus().toggleBold().run()"
                :active="editor.isActive('bold')"
                :icon="BoldIcon"
                tooltip="عريض (Ctrl+B)"
            />
            <ToolbarButton 
                @click="editor.chain().focus().toggleItalic().run()"
                :active="editor.isActive('italic')"
                :icon="ItalicIcon"
                tooltip="مائل (Ctrl+I)"
            />
            <ToolbarButton 
                @click="editor.chain().focus().toggleUnderline().run()"
                :active="editor.isActive('underline')"
                :icon="UnderlineIcon"
                tooltip="تحته خط (Ctrl+U)"
            />
            <ToolbarButton 
                @click="editor.chain().focus().toggleStrike().run()"
                :active="editor.isActive('strike')"
                :icon="StrikethroughIcon"
                tooltip="مشطوب"
            />
        </div>

        <!-- Alignment -->
        <div class="flex items-center gap-1 px-1 border-l border-white/20 dark:border-white/10 ml-1">
            <ToolbarButton 
                @click="editor.chain().focus().setTextAlign('right').run()"
                :active="editor.isActive({ textAlign: 'right' })"
                :icon="AlignRightIcon"
                tooltip="محاذاة لليمين"
            />
            <ToolbarButton 
                @click="editor.chain().focus().setTextAlign('center').run()"
                :active="editor.isActive({ textAlign: 'center' })"
                :icon="AlignCenterIcon"
                tooltip="توسيط"
            />
            <ToolbarButton 
                @click="editor.chain().focus().setTextAlign('left').run()"
                :active="editor.isActive({ textAlign: 'left' })"
                :icon="AlignLeftIcon"
                tooltip="محاذاة لليسار"
            />
             <ToolbarButton 
                @click="editor.chain().focus().setTextAlign('justify').run()"
                :active="editor.isActive({ textAlign: 'justify' })"
                :icon="AlignJustifyIcon"
                tooltip="ضبط النص"
            />
        </div>

        <!-- Lists -->
        <div class="flex items-center gap-1 px-1 border-l border-white/20 dark:border-white/10 ml-1">
            <ToolbarButton 
                @click="editor.chain().focus().toggleBulletList().run()"
                :active="editor.isActive('bulletList')"
                :icon="ListIcon"
                tooltip="قائمة منقطة"
            />
            <ToolbarButton 
                @click="editor.chain().focus().toggleOrderedList().run()"
                :active="editor.isActive('orderedList')"
                :icon="ListOrderedIcon"
                tooltip="قائمة رقمية"
            />
        </div>

        <!-- Scripts -->
        <div class="flex items-center gap-1 px-1 border-l border-white/20 dark:border-white/10 ml-1">
            <ToolbarButton 
                @click="editor.chain().focus().toggleSuperscript().run()"
                :active="editor.isActive('superscript')"
                :icon="SuperscriptIcon"
                tooltip="نص علوي"
            />
            <ToolbarButton 
                @click="editor.chain().focus().toggleSubscript().run()"
                :active="editor.isActive('subscript')"
                :icon="SubscriptIcon"
                tooltip="نص سفلي"
            />
        </div>

        <!-- Blocks -->
        <div class="flex items-center gap-1 px-1">
            <ToolbarButton 
                @click="editor.chain().focus().toggleBlockquote().run()"
                :active="editor.isActive('blockquote')"
                :icon="QuoteIcon"
                tooltip="اقتباس"
            />
            <ToolbarButton 
                @click="editor.chain().focus().toggleCodeBlock().run()"
                :active="editor.isActive('codeBlock')"
                :icon="CodeIcon"
                tooltip="كتلة برمجية"
            />
            <ToolbarButton 
                @click="editor.chain().focus().setHorizontalRule().run()"
                :icon="MinusIcon"
                tooltip="فاصل أفقي"
            />
        </div>
    </div>
</template>

<script setup>
import ToolbarButton from './ToolbarButton.vue'
import { 
    BoldIcon, 
    ItalicIcon, 
    UnderlineIcon, 
    StrikethroughIcon,
    AlignRightIcon,
    AlignCenterIcon,
    AlignLeftIcon,
    AlignJustifyIcon,
    ListIcon,
    ListOrderedIcon,
    SuperscriptIcon,
    SubscriptIcon,
    QuoteIcon,
    CodeIcon,
    MinusIcon
} from 'lucide-vue-next'

defineProps({
    editor: Object
})
</script>

<style scoped>
.editor-toolbar {
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
