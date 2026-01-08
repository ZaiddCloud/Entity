# Editor Technology

**الحالة:** ✅ قيد الاستخدام

**الهدف:** محرر نصوص غني بالميزات باستخدام Tiptap مع إضافات مخصصة للتراث الإسلامي.

**المكونات الرئيسية:**

## Core/
- `TiptapEditor.vue`: المحرر الرئيسي
- `EditorConfig.js`: إعدادات Tiptap
- `EditorStore.js`: حالة المحرر (Pinia)

## Toolbar/
- `EditorToolbar.vue`: شريط الأدوات الكامل

## Extensions/
### Poetry/
- PoetryExtension.js
- PoetryNodeView.vue
- PoetryModal.vue

### Quran/
- QuranExtension.js
- QuranNodeView.vue
- QuranModal.vue

### Footnotes/
- FootnoteExtension.js
- FootnoteNodeView.vue
- FootnoteModal.vue

### Timestamp/
- TimestampExtension.js
- TimestampNodeView.vue

## Modals/
- SearchReplaceModal.vue
- SpecialCharsModal.vue
- ExportModal.vue

**الاستخدام:**
```javascript
import TiptapEditor from '@/Technologies/Editor/Core/TiptapEditor.vue'
import EditorToolbar from '@/Technologies/Editor/Toolbar/EditorToolbar.vue'
```

**الميزات:**
- تنسيق النصوص (Bold, Italic, Underline)
- العناوين والقوائم
- إدراج شعر بتنسيق خاص
- إدراج آيات قرآنية
- الحواشي العلمية
- التوقيتات الزمنية (للربط بالصوتيات)
- البحث والاستبدال
- التصدير (PDF, DOCX)
