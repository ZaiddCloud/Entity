# Manuscripter Technology (V2 Modular) 📜

**الحالة:** ✅ مستقر (Stable)
**الإصدار:** 2.0 (Modular Refactor)

تقنية "المانسكريبتر" هي نظام متقدم لعرض وتصفح المخطوطات، يدعم القراءة المتسلسلة، المقارنة بين النسخ، والتكبير الدقيق. تم إعادة بنائها بالكامل لتكون معيارية (Modular) وتعتمد على Pinia لإدارة الحالة.

---

## 🏗️ الهيكلية (Architecture)

### 1. الذاكرة (The Brain) 🧠
**المسار:** `Store/ManuscriptStore.js`
إدارة مركزية للحالة باستخدام Pinia.
- **State:** `shotNumber`, `viewMode` (List/Grid/Default), `isCompareMode`.
- **Logic:** حساب الصفحات، إدارة النسخ المتعددة (Siblings)، ومنطق المقارنة.

### 2. المنطق (The Logic) ⚙️
**المسار:** `Composables/useManuscript.js`
ملف Composable يحتوي على المنطق المعقد والخالص (Pure Logic) لفصل الكود عن الواجهة.
- `parseFilename(url)`: استخراج أسماء الملفات بأمان.
- `calculateResize(...)`: معادلات تغيير حجم النوافذ (Split Pane) في وضع المقارنة.

### 3. الواجهة (The UI) 🎨
**المسار:** `UI/*.vue`
مكونات عرض "غبية" (Dumb Components) تعتمد على الـ Store فقط.

| المكون | الوصف |
|--------|-------|
| `ManuscriptHeader` | الشريط العلوي. يحتوي على أزرار التنقل، اختيار النسخ، وتبديل الأوضاع. |
| `ManuscriptFooter` | الشريط السفلي. يحتوي على شريط الفيلم (Filmstrip) وأدوات التكبير. |
| `SingleView` | وضع القراءة العمودية التقليدي (قائمة صور). |
| `CompareView` | وضع المقارنة (شاشتان أو أكثر) مع إمكانية تغيير الحجم بالسحب. |
| `GridView` | وضع الشبكة (Contact Sheet) لعرض كافة اللقطات كمصغرات. |
| **`DetailViewer`** | (جديد) عارض دقيق بملء الشاشة يدعم **Pan & Zoom**. |

---

## 🚀 الاستخدام (Usage)

### 1. العارض الرئيسي (Full Reader)
يُستخدم في صفحات قراءة المخطوطات الكاملة. يقوم بتهيئة الـ Store تلقائيًا.

```html
<script setup>
import ManuscriptClient from '@/Technologies/Manuscripter/ManuscriptClient.vue';
</script>

<template>
    <ManuscriptClient 
        :manuscript="manuscriptData"
        :siblings="siblingsData"
        active-slug="page-1"
    />
</template>
```

### 2. العارض الدقيق (Detail Viewer)
يُستخدم في الـ Editor أو لعرض صفحة واحدة بشكل منفصل.

```html
<script setup>
import DetailViewer from '@/Technologies/Manuscripter/UI/DetailViewer.vue';
</script>

<template>
    <DetailViewer 
        :resource="manuscriptData"
        :current-node="currentPageData"
    />
</template>
```

---

## 🛠️ دليل المطور (Developer Guide)

*   **لإضافة ميزة جديدة:** ابدأ بإضافتها في `ManuscriptStore` أولاً، ثم اعرضها في المكون المناسب في `UI/`.
*   **تصحيح الأخطاء (Debugging):** راقب الحالة في Vue DevTools تحت اسم المتجر `manuscript`.
*   **الاتجاه (RTL):** النظام مصمم ليدعم `dir="rtl"` بشكل افتراضي. جميع الحسابات في `useManuscript` تأخذ هذا بعين الاعتبار.
