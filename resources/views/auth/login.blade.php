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
                <div class="flex h-20 w-40 items-center justify-center rounded-2xl bg-slate-50 p-2 ring-1 ring-slate-200 sm:w-48">
                    <img src="{{ asset('gemini-svg.svg') }}" alt="Logo OncoLentes" class="h-full w-full object-contain" />
                </div>
                <div>
                    <h1 class="text-xl font-black text-slate-900">Acesso ao painel</h1>
                </div>
            </div>

            <p class="mt-5 text-sm text-slate-600">Entre para continuar a triagem e acompanhar os resultados do fluxo territorial.</p>

            <form action="{{ route('dashboard') }}" method="GET" class="mt-6 space-y-4">
                <label class="block text-sm font-semibold text-slate-700">
                    E-mail
                    <input type="email" value="admin@oncolentes.local" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-[#427fe2] focus:outline-none focus:ring-2 focus:ring-[#dce8ff]" />
                </label>

                <label class="block text-sm font-semibold text-slate-700">
                    Senha
                    <input type="password" value="12345678" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-[#427fe2] focus:outline-none focus:ring-2 focus:ring-[#dce8ff]" />
                </label>

                <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-[#0c2d59] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#427fe2]">
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
