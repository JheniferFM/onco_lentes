<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OncoLentes | Triagem Inteligente SUS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-800">
    <main class="mx-auto flex max-w-5xl flex-col gap-6 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
        <header class="rounded-[2rem] border border-slate-200 bg-gradient-to-r from-sky-800 via-blue-700 to-indigo-700 p-6 shadow-xl shadow-slate-300/70">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/15 ring-1 ring-white/25">
                        <svg viewBox="0 0 64 64" class="h-9 w-9 text-white" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="8" y="8" width="48" height="48" rx="16" fill="currentColor" fill-opacity="0.14"/>
                            <path d="M20 34C20 24.6112 27.6112 17 37 17H41C45.4183 17 49 20.5817 49 25V39C49 43.4183 45.4183 47 41 47H31C25.4772 47 21 42.5228 21 37V34H20Z" fill="white"/>
                            <path d="M28 26H38" stroke="#0f172a" stroke-width="3" stroke-linecap="round"/>
                            <path d="M28 33H36" stroke="#0f172a" stroke-width="3" stroke-linecap="round"/>
                            <path d="M28 40H32" stroke="#0f172a" stroke-width="3" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-black leading-tight text-white sm:text-3xl">
                            Fluxo assistido para captura e triagem de lesões cutâneas
                        </h1>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('login') }}" class="inline-flex items-center rounded-2xl border border-white/20 bg-white/10 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/20">
                        Entrar
                    </a>
                    <div class="rounded-2xl border border-white/20 bg-white/10 px-4 py-3 text-sm text-white backdrop-blur">
                        <p class="font-semibold">Progresso</p>
                        <p id="progressLabel" class="text-sky-50">1 de 5</p>
                    </div>
                </div>
            </div>

            <div class="mt-5 h-2 rounded-full bg-white/20">
                <div id="progressBar" class="h-2 w-[20%] rounded-full bg-white transition-all duration-300"></div>
            </div>
        </header>

        <section class="rounded-[2rem] border border-slate-200 bg-white p-4 text-slate-900 shadow-xl shadow-slate-200/80 sm:p-6">
            @if (session('erro'))
                <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                    {{ session('erro') }}
                    @if (session('erro_detalhe'))
                        <p class="mt-2 rounded-lg bg-red-100 px-2 py-1 text-xs text-red-800">
                            Detalhe técnico: {{ session('erro_detalhe') }}
                        </p>
                    @endif
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 rounded-2xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">
                    <p class="font-semibold">Verifique os campos abaixo:</p>
                    <ul class="mt-2 list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="oncolentesWizard" class="space-y-5" action="{{ route('oncolentes.analisar') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="cidade_estado" id="cidadeEstadoHidden">

                <div data-step="0" class="space-y-4">
                    <div class="flex items-center gap-2 text-sm font-semibold text-sky-700">
                        <span class="rounded-full bg-sky-100 px-2.5 py-1">1</span>
                        Boas-vindas e identificação rápida
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="block text-sm font-semibold text-slate-700">
                            Nome do paciente
                            <input id="nome" name="nome" type="text" value="{{ old('nome') }}" required class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200">
                        </label>
                        <label class="block text-sm font-semibold text-slate-700">
                            Idade
                            <input id="idade" name="idade" type="number" min="0" max="120" value="{{ old('idade') }}" required class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200">
                        </label>
                        <label class="block text-sm font-semibold text-slate-700">
                            CPF / Cartão SUS
                            <input type="text" placeholder="Ex.: 12345678900" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200">
                        </label>
                        <label class="block text-sm font-semibold text-slate-700">
                            Cidade / Estado
                            <input id="cidadeEstadoInput" type="text" placeholder="Ex.: Recife - PE" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200">
                        </label>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4 text-sm text-slate-600">
                        <p class="font-semibold text-slate-900">Orientação rápida</p>
                        <p class="mt-1">Use este primeiro passo para registrar os dados básicos do paciente e preparar a triagem territorial.</p>
                    </div>
                </div>

                <div data-step="1" class="hidden space-y-4">
                    <div class="flex items-center gap-2 text-sm font-semibold text-sky-700">
                        <span class="rounded-full bg-sky-100 px-2.5 py-1">2</span>
                        Instruções do kit
                    </div>
                    <div class="grid gap-3 md:grid-cols-3">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="font-semibold text-slate-900">1. Acople a lente macro</p>
                            <p class="mt-2 text-sm text-slate-600">Fixe a lente no celular com cuidado para preservar o enquadramento.</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="font-semibold text-slate-900">2. Posicione a régua</p>
                            <p class="mt-2 text-sm text-slate-600">Coloque a régua clínica de referência ao lado da lesão para padronizar a imagem.</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="font-semibold text-slate-900">3. Garanta boa iluminação</p>
                            <p class="mt-2 text-sm text-slate-600">Use luz natural ou uniforme para reduzir sombras e melhorar a análise.</p>
                        </div>
                    </div>
                </div>

                <div data-step="2" class="hidden space-y-4">
                    <div class="flex items-center gap-2 text-sm font-semibold text-sky-700">
                        <span class="rounded-full bg-sky-100 px-2.5 py-1">3</span>
                        Câmera inteligente
                    </div>
                    <div class="rounded-3xl border border-slate-200 bg-slate-950 p-4 text-white">
                        <div class="relative overflow-hidden rounded-2xl border border-white/20 bg-slate-900 p-4">
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="h-40 w-40 rounded-full border-2 border-cyan-400/80"></div>
                                <div class="absolute h-56 w-56 rounded-full border border-white/20"></div>
                            </div>
                            <div class="relative z-10 space-y-3">
                                <p class="text-sm font-semibold text-sky-300">Alinhamento recomendado</p>
                                <p class="text-xs text-slate-300">Centralize a lesão e mantenha a régua visível para fins de referência.</p>
                            </div>
                        </div>
                        <div class="mt-4 grid gap-3 md:grid-cols-[1.3fr_0.7fr]">
                            <label class="block text-sm font-semibold text-slate-200">
                                Foto da lesão (lente macro)
                                <input id="imagem" name="imagem" type="file" accept=".jpg,.jpeg,.png,.webp" required class="mt-2 block w-full rounded-2xl border border-slate-700 bg-slate-900 px-3 py-3 text-sm text-slate-100 file:mr-3 file:rounded-xl file:border-0 file:bg-sky-600 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white">
                            </label>
                            <div class="rounded-2xl border border-sky-200 bg-sky-50 p-3 text-sm text-sky-900">
                                <p class="font-semibold">Validação local</p>
                                <ul class="mt-2 space-y-2 text-xs text-sky-800/90">
                                    <li id="validationFocus" class="flex items-center justify-between rounded-xl border border-white/10 bg-white/5 px-2 py-2">
                                        <span class="flex items-center gap-2">
                                            <span id="validationFocusIcon" class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-white/10 text-[10px]">•</span>
                                            <span>Foco nítido</span>
                                        </span>
                                        <span id="validationFocusStatus" class="text-sky-700">Aguardando</span>
                                    </li>
                                    <li id="validationLighting" class="flex items-center justify-between rounded-xl border border-white/10 bg-white/5 px-2 py-2">
                                        <span class="flex items-center gap-2">
                                            <span id="validationLightingIcon" class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-white/10 text-[10px]">•</span>
                                            <span>Iluminação uniforme</span>
                                        </span>
                                        <span id="validationLightingStatus" class="text-sky-700">Aguardando</span>
                                    </li>
                                    <li id="validationRuler" class="flex items-center justify-between rounded-xl border border-white/10 bg-white/5 px-2 py-2">
                                        <span class="flex items-center gap-2">
                                            <span id="validationRulerIcon" class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-white/10 text-[10px]">•</span>
                                            <span>Régua visível</span>
                                        </span>
                                        <span id="validationRulerStatus" class="text-sky-700">Aguardando</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div id="imagePreview" class="mt-4 hidden rounded-2xl border border-slate-700 bg-slate-900 p-3">
                            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-sky-600">Pré-visualização</p>
                            <img id="previewImage" alt="Pré-visualização da imagem" class="mt-2 max-h-56 w-full rounded-xl object-contain">
                        </div>
                    </div>
                </div>

                <div data-step="3" class="hidden space-y-4">
                    <div class="flex items-center gap-2 text-sm font-semibold text-sky-700">
                        <span class="rounded-full bg-sky-100 px-2.5 py-1">4</span>
                        Mini-anamnese
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm font-semibold text-slate-700">
                            Há coceira na lesão?
                            <select name="anamnese[coceira]" class="mt-2 w-full rounded-2xl border border-slate-300 px-3 py-2 text-sm">
                                <option value="">Selecione</option>
                                <option value="sim">Sim</option>
                                <option value="nao">Não</option>
                                <option value="nao_sabe">Não sei</option>
                            </select>
                        </label>
                        <label class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm font-semibold text-slate-700">
                            Há sangramento?
                            <select name="anamnese[sangramento]" class="mt-2 w-full rounded-2xl border border-slate-300 px-3 py-2 text-sm">
                                <option value="">Selecione</option>
                                <option value="sim">Sim</option>
                                <option value="nao">Não</option>
                                <option value="nao_sabe">Não sei</option>
                            </select>
                        </label>
                        <label class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm font-semibold text-slate-700">
                            Evolução nas últimas semanas?
                            <select name="anamnese[evolucao]" class="mt-2 w-full rounded-2xl border border-slate-300 px-3 py-2 text-sm">
                                <option value="">Selecione</option>
                                <option value="sim">Sim</option>
                                <option value="nao">Não</option>
                                <option value="nao_sabe">Não sei</option>
                            </select>
                        </label>
                        <label class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm font-semibold text-slate-700">
                            Há dor ou sensibilidade?
                            <select name="anamnese[dor]" class="mt-2 w-full rounded-2xl border border-slate-300 px-3 py-2 text-sm">
                                <option value="">Selecione</option>
                                <option value="sim">Sim</option>
                                <option value="nao">Não</option>
                                <option value="nao_sabe">Não sei</option>
                            </select>
                        </label>
                    </div>
                    <label class="block text-sm font-semibold text-slate-700">
                        Observações adicionais
                        <textarea name="anamnese[observacoes]" rows="3" class="mt-2 w-full rounded-2xl border border-slate-300 px-3 py-2 text-sm"></textarea>
                    </label>
                </div>

                <div data-step="4" class="hidden space-y-4">
                    <div class="flex items-center gap-2 text-sm font-semibold text-sky-700">
                        <span class="rounded-full bg-sky-100 px-2.5 py-1">5</span>
                        Sincronização e resultado
                    </div>
                    <div class="rounded-3xl border border-sky-200 bg-sky-50 p-4">
                        <p class="text-sm font-semibold text-sky-900">Resumo da triagem</p>
                        <div id="summaryBox" class="mt-3 space-y-2 text-sm text-slate-700"></div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
                        <p class="font-semibold text-slate-900">Protocolo de triagem</p>
                        <p class="mt-2">A análise será enviada para processamento e o resultado será exibido com o risco estimado, a confiança e o aviso legal correspondente.</p>
                    </div>
                    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-sky-700 px-5 py-3 text-sm font-bold text-white transition hover:bg-sky-600 focus:outline-none focus:ring-2 focus:ring-sky-300">
                        Enviar análise
                    </button>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 pt-4">
                    <button type="button" id="prevBtn" class="rounded-2xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Voltar</button>
                    <button type="button" id="nextBtn" class="rounded-2xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Continuar</button>
                </div>
            </form>
        </section>
    </main>

    <script>
        const steps = Array.from(document.querySelectorAll('[data-step]'));
        const nextBtn = document.getElementById('nextBtn');
        const prevBtn = document.getElementById('prevBtn');
        const progressBar = document.getElementById('progressBar');
        const progressLabel = document.getElementById('progressLabel');
        const summaryBox = document.getElementById('summaryBox');
        const cityStateInput = document.getElementById('cidadeEstadoInput');
        const cityStateHidden = document.getElementById('cidadeEstadoHidden');
        const imageInput = document.getElementById('imagem');
        const imagePreview = document.getElementById('imagePreview');
        const previewImage = document.getElementById('previewImage');
        let currentStep = 0;

        function updateValidationState() {
            const hasFile = Boolean(imageInput.files?.length);
            const items = [
                ['validationFocus', 'validationFocusIcon', 'validationFocusStatus'],
                ['validationLighting', 'validationLightingIcon', 'validationLightingStatus'],
                ['validationRuler', 'validationRulerIcon', 'validationRulerStatus'],
            ];

            items.forEach(([rowId, iconId, statusId]) => {
                const row = document.getElementById(rowId);
                const icon = document.getElementById(iconId);
                const status = document.getElementById(statusId);

                if (!row || !icon || !status) {
                    return;
                }

                if (hasFile) {
                    row.classList.add('border-sky-400/50', 'bg-sky-500/15');
                    row.classList.remove('border-white/10', 'bg-white/5');
                    icon.className = 'inline-flex h-5 w-5 items-center justify-center rounded-full bg-sky-600 text-[10px] font-bold text-white';
                    icon.textContent = '✓';
                    status.className = 'text-sky-700';
                    status.textContent = 'Validado';
                } else {
                    row.classList.remove('border-sky-400/50', 'bg-sky-500/15');
                    row.classList.add('border-white/10', 'bg-white/5');
                    icon.className = 'inline-flex h-5 w-5 items-center justify-center rounded-full bg-white/10 text-[10px]';
                    icon.textContent = '•';
                    status.className = 'text-sky-700';
                    status.textContent = 'Aguardando';
                }
            });
        }

        function updateStep() {
            steps.forEach((step, index) => {
                step.classList.toggle('hidden', index !== currentStep);
            });

            prevBtn.disabled = currentStep === 0;
            prevBtn.classList.toggle('opacity-50', currentStep === 0);
            nextBtn.classList.toggle('hidden', currentStep === steps.length - 1);

            const progress = ((currentStep + 1) / steps.length) * 100;
            const visualProgress = Math.min(100, Math.max(8, progress + 2));
            progressBar.style.width = `${visualProgress}%`;
            progressLabel.textContent = `Etapa ${currentStep + 1} de ${steps.length}`;
            updateSummary();
            updateValidationState();
        }

        function updateSummary() {
            const nome = document.getElementById('nome').value || 'Não informado';
            const idade = document.getElementById('idade').value || 'Não informado';
            const cidadeEstado = cityStateInput.value || 'Não informado';
            const imagemLabel = imageInput.files?.[0]?.name || 'Ainda não enviada';

            summaryBox.innerHTML = `
                <p><span class="font-semibold">Paciente:</span> ${nome}</p>
                <p><span class="font-semibold">Idade:</span> ${idade} anos</p>
                <p><span class="font-semibold">Região:</span> ${cidadeEstado}</p>
                <p><span class="font-semibold">Imagem:</span> ${imagemLabel}</p>
            `;
        }

        nextBtn.addEventListener('click', () => {
            if (currentStep < steps.length - 1) {
                currentStep += 1;
                updateStep();
            }
        });

        prevBtn.addEventListener('click', () => {
            if (currentStep > 0) {
                currentStep -= 1;
                updateStep();
            }
        });

        ['input', 'change'].forEach((eventName) => {
            document.getElementById('oncolentesWizard').addEventListener(eventName, (event) => {
                if (event.target.id === 'cidadeEstadoInput') {
                    cityStateHidden.value = event.target.value;
                }
                updateSummary();
            });
        });

        imageInput.addEventListener('change', () => {
            const file = imageInput.files?.[0];
            if (!file) {
                imagePreview.classList.add('hidden');
                updateValidationState();
                return;
            }

            const reader = new FileReader();
            reader.onload = (event) => {
                previewImage.src = event.target?.result;
                imagePreview.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
            updateSummary();
            updateValidationState();
        });

        document.getElementById('oncolentesWizard').addEventListener('submit', () => {
            cityStateHidden.value = cityStateInput.value;
        });

        updateStep();
    </script>
</body>
</html>
