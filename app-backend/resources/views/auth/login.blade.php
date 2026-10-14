<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logbook Digital IQC - Masuk</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <style>
        * { font-family: 'Inter', sans-serif; }
        .material-symbols-outlined {
            font-family: 'Material Symbols Outlined';
            font-weight: normal;
            font-style: normal;
            font-size: 20px;
            line-height: 1;
            letter-spacing: normal;
            text-transform: none;
            display: inline-block;
            white-space: nowrap;
            word-wrap: normal;
            direction: ltr;
            -webkit-font-feature-settings: 'liga';
            font-feature-settings: 'liga';
            -webkit-font-smoothing: antialiased;
            user-select: none;
        }
        body {
            background-color: #f1f5f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            position: relative;
            overflow: hidden;
        }
        .glow-bg-1 {
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.12) 0%, transparent 70%);
            top: -150px;
            left: -150px;
            border-radius: 50%;
            pointer-events: none;
        }
        .glow-bg-2 {
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.10) 0%, transparent 70%);
            bottom: -150px;
            right: -150px;
            border-radius: 50%;
            pointer-events: none;
        }
        .card {
            width: 100%;
            max-width: 440px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            box-shadow: 0 8px 40px rgba(99,102,241,0.10), 0 2px 8px rgba(0,0,0,0.06);
            padding: 2rem;
            position: relative;
            z-index: 10;
        }
        .logo-wrap {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            background: linear-gradient(135deg, #4f46e5, #3b82f6);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            box-shadow: 0 8px 24px rgba(79, 70, 229, 0.35);
        }
        .logo-wrap .material-symbols-outlined {
            font-size: 28px;
            color: white;
        }
        .form-group {
            margin-bottom: 1.1rem;
        }
        .form-label {
            display: block;
            font-size: 0.7rem;
            font-weight: 600;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 0.4rem;
        }
        .input-wrap {
            position: relative;
        }
        .input-icon {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 16px !important;   /* kecilkan icon agar tidak overlap */
            line-height: 1;
            pointer-events: none;
            z-index: 1;
        }
        .form-input {
            width: 100%;
            background: #f8fafc !important;
            background-color: #f8fafc !important;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.7rem 0.9rem;
            font-size: 0.875rem;
            color: #0f172a !important;
            outline: none;
            transition: border-color 0.15s, box-shadow 0.15s;
            box-sizing: border-box;
            -webkit-appearance: none;
        }
        .form-input::placeholder { color: #94a3b8 !important; }
        .form-input:-webkit-autofill,
        .form-input:-webkit-autofill:hover,
        .form-input:-webkit-autofill:focus {
            -webkit-text-fill-color: #0f172a !important;
            -webkit-box-shadow: 0 0 0px 1000px #f8fafc inset !important;
        }
        .form-input:focus {
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
            background: #ffffff !important;
        }
        select.form-input {
            cursor: pointer;
            color-scheme: light;
            appearance: auto;
        }
        .btn-primary {
            width: 100%;
            padding: 0.8rem 1.5rem;
            background: #4f46e5;
            color: white;
            font-weight: 700;
            font-size: 0.875rem;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background 0.15s, transform 0.1s;
            box-shadow: 0 4px 16px rgba(79, 70, 229, 0.4);
            margin-top: 0.5rem;
        }
        .btn-primary:hover { background: #4338ca; }
        .btn-primary:active { transform: scale(0.99); }
        .btn-primary .material-symbols-outlined {
            font-size: 20px;
        }
        .alert-box {
            padding: 0.7rem 0.9rem;
            border-radius: 10px;
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 1rem;
        }
        .alert-info {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1d4ed8;
        }
        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
        }
        .remember-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        .remember-label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.75rem;
            color: #64748b;
            cursor: pointer;
        }
        .remember-label:hover { color: #334155; }
        .help-link {
            font-size: 0.75rem;
            color: #6366f1;
            text-decoration: none;
        }
        .help-link:hover { color: #818cf8; }
    </style>
</head>
<body>
    <div class="glow-bg-1"></div>
    <div class="glow-bg-2"></div>

    <div class="card">

        <!-- Logo & Title -->
        <div style="text-align: center; margin-bottom: 1.75rem;">
            <div class="logo-wrap">
                <span class="material-symbols-outlined">fact_check</span>
            </div>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: #0f172a; margin: 0 0 4px;">Logbook Digital IQC</h1>
            <p style="font-size: 0.75rem; color: #64748b; margin: 0;">Incoming Component Quality Inspection Portal</p>
        </div>

        @if(session('info'))
            <div class="alert-box alert-info">
                <span class="material-symbols-outlined" style="font-size:16px;">info</span>
                {{ session('info') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert-box alert-error">
                <span class="material-symbols-outlined" style="font-size:16px;">error</span>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <!-- Login Form -->
        <form action="{{ route('login.post') }}" method="POST">
            @csrf

            <!-- NIK -->
            <div class="form-group">
                <label class="form-label" for="nik">NIK / ID Operator</label>
                <input type="text" id="nik" name="nik" class="form-input"
                    value="{{ old('nik') }}"
                    placeholder="Masukkan NIK akun Anda" required autofocus>
            </div>

            <!-- Password -->
            <div class="form-group">
                <label class="form-label" for="password">Kata Sandi</label>
                <input type="password" id="password" name="password" class="form-input"
                    placeholder="Masukkan kata sandi" required>
            </div>

            <div class="remember-row">
                <label class="remember-label">
                    <input type="checkbox" name="remember"
                        style="width:13px;height:13px;accent-color:#6366f1;">
                    <span>Ingat sesi saya</span>
                </label>
                <a href="#" class="help-link">Bantuan IT?</a>
            </div>

            <!-- Submit -->
            <button type="submit" class="btn-primary">
                Masuk ke Logbook IQC &rarr;
            </button>
        </form>
    </div>
</body>
</html>
