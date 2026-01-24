# Common Components

**الحالة:** ✅ قيد الاستخدام

**الهدف:** مكونات مشتركة تُستخدم عبر تقنيات متعددة.

**المكونات:**

## ResourceNavigator.vue
متصفح الموارد - يسمح بالتنقل السريع بين الموارد من نفس النوع.

**الاستخدام:**
```javascript
import ResourceNavigator from '@/Technologies/Common/ResourceNavigator.vue'
```

**يُستخدم في:**
- MediaPlayer
- ManuscriptViewer
- أي مكون يحتاج تصفح الموارد

## HierarchySidebar.vue
الشريط الجانبي الهرمي - يعرض بنية الموارد بشكل شجري.

**الاستخدام:**
```javascript
import HierarchySidebar from '@/Technologies/Common/HierarchySidebar.vue'
```

**يُستخدم في:**
- صفحة المحرر الموحد
- عرض الكتب متعددة الفصول

---

**ملاحظة:** المكونات هنا يجب أن تكون **محايدة تماماً** ولا تعتمد على تقنية معينة.
