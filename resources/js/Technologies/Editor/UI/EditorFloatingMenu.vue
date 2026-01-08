<script setup>
import { FloatingMenu } from '@tiptap/vue-3/menus'
import ToolbarButton from '../Toolbar/Components/ToolbarButton.vue'

const props = defineProps({
    editor: {
        type: Object,
        required: true
    }
})
</script>

<template>
    <floating-menu 
        :editor="editor" 
        :tippy-options="{ duration: 100 }"
        v-if="editor"
        class="bg-white shadow-lg border border-gray-200 rounded-lg flex items-center p-1 gap-0.5"
    >
        <ToolbarButton 
            icon="ri-h-1" 
            title="عنوان 1"
            :active="editor.isActive('heading', { level: 1 })" 
            @click="editor.chain().focus().toggleHeading({ level: 1 }).run()"
        />
        <ToolbarButton 
            icon="ri-h-2" 
            title="عنوان 2"
            :active="editor.isActive('heading', { level: 2 })" 
            @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
        />
        <ToolbarButton 
            icon="ri-list-unordered" 
            title="قائمة نقطية"
            :active="editor.isActive('bulletList')" 
            @click="editor.chain().focus().toggleBulletList().run()"
        />
        <ToolbarButton 
            icon="ri-image-add-line" 
            title="صورة"
            @click="editor.chain().focus().setImage({ src: 'https://placehold.co/600x400' }).run()"
        />
    </floating-menu>
</template>
