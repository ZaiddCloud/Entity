# BookEditor Testing Plan (Cumulative TDD) 🧪🛡️

هذا المستند يمثل المخطط النهائي والاختباري الشامل لبناء الـ BookEditor، مع ضمان عدم حذف أي اختبار تم الاتفاق عليه سابقاً، وإضافة اختبارات الوحدات والوسوم المطلوبة.

## 1. اختبارات الواجهة الخلفية (Backend - PHPUnit)

### `MarkdownStructureParserTest.php` (تحليل الهيكل التلقائي)
يختبر كيفية تحويل ملف Markdown واحد إلى هيكل شجري بناءً على تدرج العناوين.
*   `test_parser_detects_heading_levels_accurately`: التأكد من رصد مستويات العناوين (H1-H8) بدقة.
*   `test_parser_creates_semantic_hierarchy_by_depth`: التحقق من بناء الهيكل الشجري بناءً على العمق النسبي للعناوين (مثلاً H3 يتبع H2).
*   `test_parser_attaches_paragraphs_to_correct_parent`: التأكد من أن النصوص تتبع أقرب عنوان فوقها دون خلل.
*   `test_parser_handles_ultra_deep_nesting_up_to_h8`: التأكد من استقرار الهيكل عند وجود مستويات عميقة جداً تصل إلى المستوى الثامن.

### `HierarchyManagementTest.php` (إدارة الهيكل الشاملة)
تستهدف التأكد من أن النظام يتعامل مع جميع أنواع الأبناء (الذرات) والمستويات العميقة.
*   `test_user_can_manage_all_unit_types`: دعم كامل لـ (sub-book, Part, Bab, Chapter, Masala) حسب هيكلية البوك كونتينت.
*   `test_user_can_move_unit_between_parents`: إعادة تنظيم الهيكل الشجري (نقل أي وحدة من مستوى لآخر).
*   `test_user_can_delete_unit_recursively`: الحذف المتسلسل للوحدات وأبنائها (حذف جزء يحذف الأبواب والفصول التابعة).
*   `test_unit_reordering_with_mixed_types`: التحقق من ترتيب الوحدات عند وجود أنواع مختلفة في نفس المستوى.

### `UnitAnnotationTest.php` (وسم الوحدات والتحشية الكلية)
تستهدف الوحدات كـ "كيان" مستقل بعيداً عن كتل المحتوى الداخلية.
*   `test_user_can_apply_tags_to_specific_unit`: القدرة على وسم الوحدات (مثل وسم "مصحح"، "مترجم"، "بحاجة لمراجعة").
*   `test_user_can_add_footnote_to_unit_title`: إدراج هوامش مرتبطة بعنوان الوحدة مباشرة.
*   `test_user_can_add_internal_comments_to_unit_metadata`: إضافة تعليقات إدارية غير ظاهرة في القارئ مخزنة في الميتا-داتا.

### `SyncProtectionTest.php` (حماية المزامنة)
*   `test_storage_sync_skips_manually_edited_units`: حماية التعديلات اليدوية من الضياع.
*   `test_conflict_alert_triggered_when_sync_hits_manual_edits`: إخطار المستخدم بوجود تعارض مع ملف خارجي.

### `VersioningTest.php` (تاريخ النسخ)
*   `test_save_content_creates_new_version_snapshot`: أخذ لقطة فورية عند كل عملية حفظ.
*   `test_restore_version_updates_current_block_content`: اختبار استعادة الإصدارات بدقة.

## 2. اختبارات المتصفح (Frontend - Laravel Dusk)

### المحور الأول: الهوية وتجربة المستخدم (`EditorUXTest.php`)
*   `test_toolbar_buttons_apply_formatting`: التحقق من تغطية 100% للأدوات:
    - تنسيق النص: عريض (Bold)، مائل (Italic)، مسطر (Underline)، مشطوب (Strike)، كود (Code)، رابط (Link).
    - التنسيق العلمي: رفع (Superscript)، خفض (Subscript).
    - المحاذاة: يمين، يسار، وسط، ضبط (Justify).
    - الأنماط: عناوين (H1-H8)، اقتباس (Quote)، كود برمج (Code Block).
    - القوائم: منقطة، مرقمة، قائمة مهام (Task List).
    - أدوات مساعدة: فاصل أفقي (Horizontal Rule)، سطر جديد (Hard Break).
*   `test_side_annotations_hover_states`: الهوامش لا تظهر إلا عند التحويم (Glassmorphism).
*   `test_side_annotations_inline_editing`: تحرير الهامش وحفظه داخل البطاقة الجانبية (CKEditor style).
*   `test_block_handles_drag_and_drop`: ترتيب الفقرات مرئياً (Editor.js style).
*   `test_floating_bubble_menu_on_selection`: ظهور شريط التنسيق السريع.
*   `test_smart_insertion_menu_ctrl_k`: قائمة الإدراج السريع لجميع أنواع البلوكات (جداول، صور، أبيات).
*   `test_auto_direction_detection_per_block`: التحقق من الكشف التلقائي عن اتجاه النص (عربي/إنجليزي) لكل فقرة.
*   `test_typography_and_font_fidelity`: التأكد من تفعيل خطوط (Amiri) و (Outfit) وتناسق الفراغات بفتراضية Milkdown.
*   `test_marker_visual_distinction`: تمييز بصري واضح للعلامات (Comment, Footnote, Tag) داخل النص.
*   `test_rtl_ui_alignment`: التأكد من انعكاس اتجاه القوائم المنبثقة ومقابض السحب في وضع الـ RTL.

### المحور الثاني: البيئة والتنقل (`NavigationTest.php`)
*   `test_clicking_edit_in_index_opens_default_inplace_mode`: التحقق من نقطة الانطلاق والوضع الافتراضي.
*   `test_switching_between_editor_modes`: التبديل بين (In-place, FullEditor, Split View) وتغيير تخطيط الواجهة.
*   `test_smart_hierarchy_navigator_granular_interaction`:
    - الانتقال المباشر عبر إدخال الأرقام (Jump-by-digit).
    - البحث والفلترة داخل القائمة المنسدلة للملاح السفلي.
    - توسيع وطي شريط التنقل السفلي.

### المحور الثالث والرابع والخامس: الميزات، التصدير، والتثبيت (`FeaturesTest.php`)
*   `test_deep_hierarchy_ui_management`: تعديل الهيكل الشجري من واجهة المحرر.
*   `test_export_workflow_selection`: واجهة اختيار نوع ونطاق التصدير.
*   `test_persistence_and_stubbing_logic` (شامل ومنهجي):
    - `test_initial_hydration`: التحقق من تحميل البيانات (Stub) بنجاح من MongoDB عند الفتح.
    - `test_local_storage_autosave`: النسخ الاحتياطي اللحظي في المتصفح.
    - `test_recovery_on_conflict`: التعامل مع "تنبيه وجود نسخة أحدث محلياً" عند فتح صفحة كانت مقطوعة.
    - `test_extension_integrity`: التأكد من أن جميع الإضافات (Extensions) "مثبتة" ومفعلة برمجياً داخل المحرك.
    - `test_data_sanitization`: تنظيف وتثبيت المحتوى الوارد لمنع الأخطاء البرمجية (Validation).
*   `test_version_browsing_and_recovery_ui`: واجهة تصفح التاريخ واستعادة النسخ.
*   `test_export_modal_and_scope_selection`: واجهة تصدير (PDF/Word/MD) وتحديد النطاق (فصل/كتاب).
*   `test_offline_persistence_indicator`: مؤشر حالة الاتصال والتخزين المحلي.

## 3. اختبارات مطابقة الخطة والدستور (Institutional Audit)
*   `test_constitutional_compliance_audit`: فحص آلي للتأكد من أن كل "محور" في الدستور له مقابل برمي في الكود أو الاختبارات.
*   `test_architectural_fidelity`: التاكد من أن بناء المكونات (Components) والمسارات (Routes) يسير بدقة وفقاً لـ `implementation_plan.md`.
*   `test_design_standards_compliance`: التدقيق في استخدام الرموز (Design Tokens) المتفق عليها (الخطوط، الألوان، تأثير Glassmorphism).
