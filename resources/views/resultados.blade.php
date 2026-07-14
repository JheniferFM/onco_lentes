<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultado da Análise | OncoLentes</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-800">
    <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
        @php
            $badgeClass = match($analysis->resultado_ia) {
                'Baixo' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                'Médio' => 'bg-amber-100 text-amber-800 border-amber-200',
                'Alto' => 'bg-red-100 text-red-800 border-red-200',
                default => 'bg-slate-100 text-slate-800 border-slate-200',
            };
            $enhancedImageUrl = str_starts_with((string) $analysis->caminho_imagem_melhorada, 'http')
                ? $analysis->caminho_imagem_melhorada
                : Storage::disk('public')->url($analysis->caminho_imagem_melhorada);
        @endphp

        <header class="rounded-2xl bg-white p-6 shadow-sm">
            <h1 class="text-2xl font-black text-slate-900">Resultado da triagem OncoLentes</h1>
            <p class="mt-2 text-sm text-slate-600">Análise assistida por IA para apoio à decisão clínica e monitoramento territorial do SUS.</p>
        </header>

        <section class="mt-6 grid gap-6 lg:grid-cols-3">
            <article class="rounded-2xl bg-white p-6 shadow-sm lg:col-span-1">
                <h2 class="text-sm font-bold uppercase tracking-wide text-slate-500">Dados do paciente</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div>
                        <dt class="font-semibold text-slate-600">Nome</dt>
                        <dd class="text-slate-900">{{ $analysis->nome }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-slate-600">Idade</dt>
                        <dd class="text-slate-900">{{ $analysis->idade }} anos</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-slate-600">Cidade/Estado</dt>
                        <dd class="text-slate-900">{{ $analysis->cidade_estado }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-slate-600">Classe original do modelo</dt>
                        <dd class="text-slate-900">{{ $labelOriginal ?? 'não informado' }}</dd>
                    </div>
                </dl>

                <div class="mt-5 rounded-xl border px-3 py-2 {{ $badgeClass }}">
                    <p class="text-xs font-bold uppercase tracking-wide">Risco estimado</p>
                    <p class="mt-1 text-xl font-extrabold">{{ $analysis->resultado_ia }}</p>
                    <p class="text-sm font-semibold">Confiança: {{ number_format($analysis->percentual_confianca, 2, ',', '.') }}%</p>
                </div>
            </article>

            <section class="rounded-2xl bg-white p-6 shadow-sm lg:col-span-2" x-data="{ comparacao: 50 }">
                <h2 class="text-sm font-bold uppercase tracking-wide text-slate-500">Comparativo visual da lesão</h2>
                <p class="mt-2 text-sm text-slate-600">Arraste o controle para comparar imagem original e versão aprimorada pelo Real-ESRGAN.</p>

                <div class="mt-4">
                    <input
                        type="range"
                        min="0"
                        max="100"
                        x-model="comparacao"
                        class="w-full accent-cyan-700"
                        aria-label="Controle de comparação entre imagens"
                    >
                </div>

                <div class="relative mt-4 h-80 overflow-hidden rounded-2xl border border-slate-200 bg-slate-200 sm:h-[28rem]">
                    <img
                        src="{{ Storage::disk('public')->url($analysis->caminho_imagem_original) }}"
                        alt="Imagem original"
                        class="absolute inset-0 h-full w-full object-contain"
                    >

                    <div class="absolute inset-0 overflow-hidden" :style="`width: ${comparacao}%`">
                        <img
                            src="{{ $enhancedImageUrl }}"
                            alt="Imagem melhorada"
                            class="h-full w-full object-contain"
                        >
                    </div>

                    <div class="pointer-events-none absolute inset-y-0 border-r-2 border-white/80" :style="`left: ${comparacao}%`"></div>

                    <div class="absolute left-3 top-3 rounded-md bg-black/55 px-2 py-1 text-xs font-semibold text-white">Original</div>
                    <div class="absolute right-3 top-3 rounded-md bg-cyan-700/90 px-2 py-1 text-xs font-semibold text-white">Melhorada</div>
                </div>
            </section>
        </section>

        @if (!empty($pipelineNotice))
            <section class="mt-6 rounded-2xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900">
                <p class="font-semibold">Observação de processamento</p>
                <p class="mt-1">{{ $pipelineNotice }}</p>
            </section>
        @endif

        <section class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
            <p class="font-semibold">Aviso legal</p>
            <p class="mt-1">
                A IA atua estritamente como ferramenta de apoio à decisão clínica e triagem territorial do SUS, não substituindo o diagnóstico médico.
            </p>
        </section>

        <div class="mt-6">
            <a
                href="{{ route('dashboard') }}"
                class="inline-flex items-center rounded-xl bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-700"
            >
                Nova análise
            </a>
        </div>
    </main>
</body>
</html>
