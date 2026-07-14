<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OncoLentes | Triagem Inteligente SUS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-800">
    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <header class="rounded-2xl bg-gradient-to-r from-cyan-700 to-teal-600 p-8 text-white shadow-lg">
            <p class="inline-flex items-center rounded-full bg-white/20 px-3 py-1 text-xs font-semibold uppercase tracking-wide">
                Hackathon do SUS 2026
            </p>
            <h1 class="mt-4 text-3xl font-black leading-tight sm:text-4xl">
                OncoLentes: rastreamento e monitoramento clínico do câncer de pele
            </h1>
            <p class="mt-3 max-w-3xl text-sm text-cyan-50 sm:text-base">
                Plataforma web para triagem territorial com apoio de IA, integrada a kit portátil com lente macro e régua para captura padronizada de lesões cutâneas.
            </p>
        </header>

        <section class="mt-8 grid gap-4 md:grid-cols-3">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-cyan-700">Dado epidemiológico</p>
                <h2 class="mt-2 text-2xl font-extrabold">30%</h2>
                <p class="mt-2 text-sm text-slate-600">
                    O câncer de pele representa 30% de todos os tumores malignos registrados no país.
                </p>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Impacto da detecção precoce</p>
                <h2 class="mt-2 text-2xl font-extrabold">&gt;90%</h2>
                <p class="mt-2 text-sm text-slate-600">
                    Se descoberto no início, a taxa de cura é superior a 90%.
                </p>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">Janela de maior risco</p>
                <h2 class="mt-2 text-2xl font-extrabold">10h às 16h</h2>
                <p class="mt-2 text-sm text-slate-600">
                    Apenas entre 10h e 16h a exposição solar é considerada mais crítica, mas a detecção precoce é essencial para todos os fototipos.
                </p>
            </article>
        </section>

        <section class="mt-8 grid gap-6 lg:grid-cols-5">
            <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
                <h2 class="text-lg font-extrabold text-slate-900">Guia de Triagem ABCDE</h2>
                <p class="mt-2 text-sm text-slate-600">
                    Referência clínica para avaliação inicial de lesões suspeitas no contexto da atenção primária e vigilância oncológica territorial.
                </p>

                <dl class="mt-5 space-y-3 text-sm">
                    <div class="rounded-xl bg-slate-50 p-3">
                        <dt class="font-bold text-slate-900">A - Assimetria</dt>
                        <dd class="text-slate-600">Metades da lesão com formas diferentes podem sinalizar risco.</dd>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-3">
                        <dt class="font-bold text-slate-900">B - Bordas</dt>
                        <dd class="text-slate-600">Bordas irregulares, serrilhadas ou mal definidas exigem atenção.</dd>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-3">
                        <dt class="font-bold text-slate-900">C - Cor</dt>
                        <dd class="text-slate-600">Múltiplas tonalidades na mesma lesão aumentam suspeita clínica.</dd>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-3">
                        <dt class="font-bold text-slate-900">D - Diâmetro</dt>
                        <dd class="text-slate-600">Lesões acima de 6mm devem ser avaliadas com prioridade.</dd>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-3">
                        <dt class="font-bold text-slate-900">E - Evolução</dt>
                        <dd class="text-slate-600">Mudanças rápidas em forma, cor, tamanho ou sintomas são alertas.</dd>
                    </div>
                </dl>
            </article>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-3">
                <h2 class="text-lg font-extrabold text-slate-900">Nova análise com IA</h2>
                <p class="mt-2 text-sm text-slate-600">
                    Envie a foto capturada com a lente macro do kit OncoLentes e registre dados básicos para análise territorial no SUS.
                </p>

                @if (session('erro'))
                    <div class="mt-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                        {{ session('erro') }}
                        @if (session('erro_detalhe'))
                            <p class="mt-2 rounded-md bg-red-100 px-2 py-1 text-xs text-red-800">
                                Detalhe técnico: {{ session('erro_detalhe') }}
                            </p>
                        @endif
                        <p class="mt-2 text-xs text-red-600">Use o código exibido acima para localizar a falha correspondente nos logs do Railway.</p>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">
                        <p class="font-semibold">Verifique os campos abaixo:</p>
                        <ul class="mt-2 list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form class="mt-5 grid gap-4" action="{{ route('oncolentes.analisar') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div>
                        <label for="nome" class="mb-1 block text-sm font-semibold text-slate-700">Nome do paciente</label>
                        <input
                            id="nome"
                            name="nome"
                            type="text"
                            value="{{ old('nome') }}"
                            class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-200"
                            required
                        >
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="idade" class="mb-1 block text-sm font-semibold text-slate-700">Idade</label>
                            <input
                                id="idade"
                                name="idade"
                                type="number"
                                min="0"
                                max="120"
                                value="{{ old('idade') }}"
                                class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-200"
                                required
                            >
                        </div>

                        <div>
                            <label for="cidade_estado" class="mb-1 block text-sm font-semibold text-slate-700">Região/Cidade (territorial SUS)</label>
                            <input
                                id="cidade_estado"
                                name="cidade_estado"
                                type="text"
                                value="{{ old('cidade_estado') }}"
                                placeholder="Ex.: Recife - PE"
                                class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-200"
                                required
                            >
                        </div>
                    </div>

                    <div>
                        <label for="imagem" class="mb-1 block text-sm font-semibold text-slate-700">Foto da lesão (lente macro)</label>
                        <input
                            id="imagem"
                            name="imagem"
                            type="file"
                            accept=".jpg,.jpeg,.png,.webp"
                            class="block w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2 text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-cyan-700 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-cyan-600"
                            required
                        >
                        <p class="mt-2 text-xs text-slate-500">Formatos aceitos: JPG, JPEG, PNG, WEBP. Tamanho máximo: 10MB.</p>
                    </div>

                    <button
                        type="submit"
                        class="mt-2 inline-flex items-center justify-center rounded-xl bg-cyan-700 px-5 py-3 text-sm font-bold text-white transition hover:bg-cyan-600 focus:outline-none focus:ring-2 focus:ring-cyan-300"
                    >
                        Analisar lesão
                    </button>
                </form>
            </section>
        </section>
    </main>
</body>
</html>
