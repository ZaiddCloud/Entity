import { computed, watch } from 'vue';
import { useReaderStore } from './ReaderStore';

export function useTheme() {
    const store = useReaderStore();

    const themes = {
        light: {
            bg: 'bg-white',
            text: 'text-gray-900',
            accent: 'text-blue-600',
            border: 'border-gray-200',
            button: 'bg-gray-100 hover:bg-gray-200 text-gray-700',
            sidebar: 'bg-gray-50',
            scrollbar: 'scrollbar-light'
        },
        dark: {
            bg: 'bg-gray-900',
            text: 'text-gray-100',
            accent: 'text-blue-400',
            border: 'border-gray-800',
            button: 'bg-gray-800 hover:bg-gray-700 text-gray-300',
            sidebar: 'bg-gray-950',
            scrollbar: 'scrollbar-dark'
        },
        sepia: {
            bg: 'bg-[#f4ecd8]',
            text: 'text-[#5b4636]',
            accent: 'text-[#8c6239]',
            border: 'border-[#e4dcc8]',
            button: 'bg-[#e9e0c9] hover:bg-[#dfd7bf] text-[#6b5646]',
            sidebar: 'bg-[#ebe3cf]',
            scrollbar: 'scrollbar-sepia'
        }
    };

    const currentThemeClasses = computed(() => themes[store.theme] || themes.light);

    // Update body classes for scrollbar and other global styles if needed
    watch(() => store.theme, (newTheme) => {
        // Optional: Apply global styles to body or root element
        document.documentElement.className = `theme-${newTheme}`;
    }, { immediate: true });

    return {
        themes,
        currentThemeClasses,
        setTheme: store.setTheme
    };
}
