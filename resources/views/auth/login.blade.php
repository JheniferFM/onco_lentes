<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | OncoLentes</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-800">
    <main class="mx-auto flex min-h-screen max-w-md items-center justify-center px-4 py-10">
        <div class="w-full rounded-[2rem] border border-slate-200 bg-white p-8 shadow-xl shadow-slate-200/80">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-700/10">
                    <svg viewBox="0 0 64 64" class="h-7 w-7 text-sky-700" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="8" y="8" width="48" height="48" rx="16" fill="currentColor" fill-opacity="0.12"/>
                        <path d="M20 34C20 24.6112 27.6112 17 37 17H41C45.4183 17 49 20.5817 49 25V39C49 43.4183 45.4183 47 41 47H31C25.4772 47 21 42.5228 21 37V34H20Z" fill="currentColor"/>
                        <path d="M28 26H38" stroke="#0f172a" stroke-width="3" stroke-linecap="round"/>
                        <path d="M28 33H36" stroke="#0f172a" stroke-width="3" stroke-linecap="round"/>
                        <path d="M28 40H32" stroke="#0f172a" stroke-width="3" stroke-linecap="round"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-sky-700">OncoLentes</p>
                    <h1 class="text-xl font-black text-slate-900">Acesso ao painel</h1>
                </div>
            </div>

            <p class="mt-5 text-sm text-slate-600">Entre para continuar a triagem e acompanhar os resultados do fluxo territorial.</p>

            <form action="{{ route('dashboard') }}" method="GET" class="mt-6 space-y-4">
                <label class="block text-sm font-semibold text-slate-700">
                    E-mail
                    <input type="email" value="admin@oncolentes.local" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200" />
                </label>

                <label class="block text-sm font-semibold text-slate-700">
                    Senha
                    <input type="password" value="12345678" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200" />
                </label>

                <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-sky-700 px-4 py-3 text-sm font-semibold text-white transition hover:bg-sky-600">
                    Entrar
                </button>
            </form>

            <p class="mt-4 text-center text-sm text-slate-500">
                Ainda não possui acesso? Entre em contato com a equipe do projeto.
            </p>
        </div>
    </main>
</body>
</html>
