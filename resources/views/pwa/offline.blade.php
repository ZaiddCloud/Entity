<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الملاذ الرقمي | Entity Sanctuary</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        :root {
            --color-bg: #020617;
            --color-blue: #3b82f6;
            --color-orange: #f97316;
            --color-slate-400: #94a3b8;
            --color-slate-800: #1e293b;
            --color-slate-900: #0f172a;
        }

        body {
            margin: 0;
            padding: 0;
            background-color: var(--color-bg);
            color: white;
            font-family: system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            text-align: center;
        }

        .sanctum-container {
            z-index: 10;
            max-width: 600px;
            width: 100%;
            padding: 24px;
        }

        /* Ambient Background */
        .ambient-glow {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            pointer-events: none;
        }

        .glow-blue {
            position: absolute;
            top: -20%;
            left: -10%;
            width: 60%;
            height: 60%;
            background: rgba(59, 130, 246, 0.1);
            filter: blur(120px);
            border-radius: 50%;
        }

        .glow-orange {
            position: absolute;
            bottom: -20%;
            right: -10%;
            width: 60%;
            height: 60%;
            background: rgba(249, 115, 22, 0.1);
            filter: blur(120px);
            border-radius: 50%;
        }

        /* Icon Wrapper */
        .icon-box {
            position: relative;
            display: inline-block;
            margin-bottom: 32px;
        }

        .icon-pulse {
            position: absolute;
            inset: 0;
            background: rgba(59, 130, 246, 0.2);
            filter: blur(24px);
            border-radius: 50%;
            transform: scale(1.5);
            animation: pulse-animation 2s infinite;
        }

        .icon-inner {
            position: relative;
            width: 96px;
            height: 96px;
            background: var(--color-slate-900);
            border: 1px solid rgba(59, 130, 246, 0.3);
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .icon-inner i {
            font-size: 48px;
            color: #60a5fa;
        }

        /* Typography */
        h1 {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 16px;
            letter-spacing: -0.025em;
        }

        .gradient-text {
            background: linear-gradient(to right, #60a5fa, #fb923c);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        p {
            color: var(--color-slate-400);
            font-size: 18px;
            line-height: 1.6;
            margin-bottom: 32px;
            font-weight: 300;
        }

        /* Buttons */
        .btn-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .btn {
            padding: 16px 0;
            border-radius: 16px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background-color: var(--color-blue);
            color: var(--color-bg);
            box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.2);
        }

        .btn-primary:hover {
            background-color: #60a5fa;
        }

        .btn-secondary {
            background-color: var(--color-slate-900);
            color: var(--color-orange);
            border: 1px solid rgba(249, 115, 22, 0.3);
        }

        .btn-secondary:hover {
            background-color: var(--color-slate-800);
        }

        /* Footer */
        .footer {
            margin-top: 48px;
            padding-top: 32px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 10px;
            color: var(--color-slate-400);
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .status-dot {
            width: 6px;
            height: 6px;
            background-color: var(--color-orange);
            border-radius: 50%;
            margin-right: 6px;
            animation: ping 1s cubic-bezier(0, 0, 0.2, 1) infinite;
        }

        @keyframes pulse-animation {
            0% { opacity: 0.2; transform: scale(1.5); }
            50% { opacity: 0.1; transform: scale(1.7); }
            100% { opacity: 0.2; transform: scale(1.5); }
        }

        @keyframes ping {
            75%, 100% { transform: scale(2); opacity: 0; }
        }

        /* Knowledge Library (Static fallback) */
        .knowledge-box {
            margin-bottom: 32px;
            text-align: right;
            display: none; /* Hidden unless JS injects data */
        }
    </style>
</head>
<body>
    <div class="ambient-glow">
        <div class="glow-blue"></div>
        <div class="glow-orange"></div>
    </div>

    <div class="sanctum-container">
        <div class="icon-box">
            <div class="icon-pulse"></div>
            <div class="icon-inner">
                <i class="ri-shield-flash-line"></i>
            </div>
        </div>

        <h1>أنت في <span class="gradient-text">الملاذ الرقمي</span></h1>
        
        <p>
            لقد انقطع الاتصال بالعالم الخارجي، لكن علمك لا يزال بين يديك. 
            بياناتك محفوظة بأمان تام في الذاكرة المحلية لجهازك.
        </p>

        <div class="btn-grid">
            <a href="/dashboard" class="btn btn-primary">
                <i class="ri-dashboard-3-line"></i>
                لوحة التحكم
            </a>
            
            <button onclick="window.location.reload()" class="btn btn-secondary">
                <i class="ri-refresh-line"></i>
                إعادة الاتصال
            </button>
        </div>

        <div class="footer">
            <div style="display: flex; align-items: center;">
                <div class="status-dot"></div>
                Sanctum Protocol v3.0 (Static)
            </div>
            <div style="color: var(--color-blue); font-family: monospace;">CONNECTED TO LOCAL TRUTH</div>
        </div>
    </div>

    <!-- Local Dexie for 100% offline support -->
    <script src="/js/vendor/dexie.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            if (typeof Dexie !== 'undefined') {
                try {
                    const db = new Dexie('EntityLocalDB_v2');
                    db.version(1).stores({ entities: 'id, slug, type, parent_id, updated_at, version_tag' });
                    db.version(2).stores({ content_blocks: '[node_id+segment_order], entity_id, chunk_hash, is_loaded' });
                    
                    const recent = await db.entities.orderBy('updated_at').reverse().limit(3).toArray();
                    if (recent.length > 0) {
                        // We could inject them here if we wanted to be fancy, 
                        // but keeping it simple for 100% reliability.
                        console.log('Sanctum: Local data available.');
                    }
                } catch (e) {
                    console.warn('Sanctum: Could not load local knowledge preview.', e);
                }
            }
        });
    </script>
</body>
</html>
