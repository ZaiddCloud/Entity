<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Offline Mode | Entity</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&display=swap');
        body { font-family: 'IBM Plex Sans Arabic', sans-serif; }
    </style>
</head>
<body class="bg-gray-900 text-gray-100 flex items-center justify-center min-h-screen">
    <div class="text-center p-8 bg-gray-800 rounded-2xl shadow-2xl border border-gray-700 max-w-md w-full mx-4">
        <div class="text-6xl mb-6">📡</div>
        <h1 class="text-3xl font-bold mb-4 text-emerald-400">وضع الملاذ الآمن</h1>
        <p class="text-gray-300 mb-8 text-lg leading-relaxed">
            أنت حالياً غير متصل بالإنترنت.
            <br>
            لا تقلق، يمكنك استعراض الملفات المحفوظة مسبقاً، وسيتم حفظ أي تغييرات جديدة محلياً.
        </p>
        <button onclick="window.location.reload()" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-3 px-6 rounded-xl transition-all w-full flex items-center justify-center gap-2">
            <span>🔄</span>
            <span>إعادة المحاولة</span>
        </button>
    </div>
</body>
</html>
