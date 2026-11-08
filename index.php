<?php
/**
 * ViaNexo - Portal de Rastreamento de Encomendas (Mobile First, Tailwind CSS)
 * Regras:
 * 1. O usuário deve inserir ID e CPF para acessar a área restrita
 * 2. Toda encomenda tem ID; caso o ID não exista, deve se informar que não foi encontrado
 */

$pageTitle = 'ViaNexo - Rastreamento Inteligente de Encomendas';
$activeNav = 'home';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Section & Formulário de Rastreio Seguro -->
<section class="relative overflow-hidden bg-gradient-to-b from-brand-900 via-slate-900 to-slate-900 text-white py-12 sm:py-20">
    <!-- Efeito de Fundo Decorativo -->
    <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#818cf8_1px,transparent_1px)] [background-size:16px_16px] pointer-events-none"></div>

    <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-brand-500/20 text-brand-300 border border-brand-500/30 mb-4">
                <i class="fa-solid fa-shield-halved text-brand-400"></i> Rastreamento Criptografado & LGPD
            </span>
            <h1 class="text-3xl sm:text-5xl font-black tracking-tight text-white leading-tight">
                Localize sua encomenda com o <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-400 to-indigo-200">ID ViaNexo</span>
            </h1>
            <p class="mt-4 text-sm sm:text-base text-slate-300">
                Acesse a área restrita da sua carga informando o código de rastreamento e o documento do contratante.
            </p>
        </div>

        <!-- Card de Rastreamento Seguro (Mobile-First) -->
        <div class="mt-8 sm:mt-12 max-w-xl mx-auto bg-white rounded-2xl shadow-2xl p-6 sm:p-8 text-slate-800 border border-slate-100">
            <div class="flex items-center justify-between pb-4 mb-5 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center font-bold text-sm">
                        <i class="fa-solid fa-box-open"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Autenticação do Pedido</h2>
                        <p class="text-[11px] text-slate-500">Validação segura do ID e Documento</p>
                    </div>
                </div>
                <span class="text-[11px] font-semibold text-brand-600 bg-brand-50 px-2 py-0.5 rounded-full">
                    Acesso Restrito
                </span>
            </div>

            <form action="actions/rastrear_action.php" method="POST" class="space-y-4">
                <!-- Campo 1: ID da Encomenda -->
                <div>
                    <label for="codigo_rastreio" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        <i class="fa-solid fa-barcode text-brand-600 mr-1"></i> ID da Encomenda (Código de Rastreio)
                    </label>
                    <div class="relative">
                        <input type="text" 
                               name="codigo_rastreio" 
                               id="codigo_rastreio" 
                               placeholder="Ex: VNX-BR782190" 
                               required
                               oninput="formatTrackingInput(this)"
                               class="w-full px-4 py-3 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 font-semibold tracking-wide placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all text-sm uppercase">
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                            <i class="fa-solid fa-qrcode"></i>
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Insira o ID alfanumérico emitido na contratação do frete.</p>
                </div>

                <!-- Campo 2: CPF / CNPJ com Blind Index -->
                <div>
                    <label for="documento" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        <i class="fa-solid fa-id-card text-brand-600 mr-1"></i> CPF ou CNPJ do Contratante
                    </label>
                    <div class="relative">
                        <input type="text" 
                               name="documento" 
                               id="documento" 
                               placeholder="000.000.000-00 ou 00.000.000/0000-00" 
                               required
                               oninput="maskCpfCnpj(this)"
                               class="w-full px-4 py-3 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 font-medium placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all text-sm">
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                            <i class="fa-solid fa-fingerprint"></i>
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Armazenado via hash seguro para confirmação da titularidade.</p>
                </div>

                <!-- Botão de Ação -->
                <div class="pt-2">
                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-6 py-3.5 bg-brand-600 hover:bg-brand-700 active:bg-brand-800 text-white text-sm font-bold rounded-xl shadow-lg shadow-brand-500/25 transition-all transform active:scale-[0.99]">
                        <i class="fa-solid fa-magnifying-glass"></i> Consultar Status da Carga
                    </button>
                </div>
            </form>

            <!-- Card de Teste Rápido / Demonstração -->
            <div class="mt-5 pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
                <span class="flex items-center gap-1.5 font-medium">
                    <i class="fa-solid fa-vial text-brand-500"></i> Dados de teste para demonstração:
                </span>
                <button type="button" 
                        onclick="preencherDemo()" 
                        class="text-brand-600 hover:text-brand-800 font-bold underline transition-colors cursor-pointer">
                    Preencher dados de teste
                </button>
            </div>
        </div>
    </div>
</section>

<!-- Seção Informativa de Status da Encomenda -->
<section class="py-12 bg-slate-50 border-t border-slate-200">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-8">
            <h3 class="text-xl sm:text-2xl font-bold text-slate-900">Fluxo Homologado de Status da Carga</h3>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Conheça os 5 estágios rastreados em tempo real pelo sistema ViaNexo</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- 1. Pedido Registrado -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col items-center text-center">
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl mb-3">
                    <i class="fa-solid fa-file-invoice"></i>
                </div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-blue-600 mb-1">Passo 1</span>
                <h4 class="font-bold text-slate-800 text-sm">Pedido Registrado</h4>
                <p class="text-xs text-slate-500 mt-1">ID gerado pelo contratante e dados inseridos na malha logística.</p>
            </div>

            <!-- 2. Em Preparação -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col items-center text-center">
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl mb-3">
                    <i class="fa-solid fa-boxes-packing"></i>
                </div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-600 mb-1">Passo 2</span>
                <h4 class="font-bold text-slate-800 text-sm">Em Preparação</h4>
                <p class="text-xs text-slate-500 mt-1">Carga conferida, triada e paletizada para envio seguro.</p>
            </div>

            <!-- 3. Em Transporte -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col items-center text-center">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl mb-3">
                    <i class="fa-solid fa-truck"></i>
                </div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 mb-1">Passo 3</span>
                <h4 class="font-bold text-slate-800 text-sm">Em Transporte</h4>
                <p class="text-xs text-slate-500 mt-1">Veículo em trânsito entre centros de distribuição e rotas.</p>
            </div>

            <!-- 4. Em rota de entrega -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col items-center text-center">
                <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl mb-3">
                    <i class="fa-solid fa-motorcycle"></i>
                </div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-purple-600 mb-1">Passo 4</span>
                <h4 class="font-bold text-slate-800 text-sm">Em rota de entrega</h4>
                <p class="text-xs text-slate-500 mt-1">Pacote despachado para entrega no endereço final do destinatário.</p>
            </div>

            <!-- 5. Entrega -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col items-center text-center">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl mb-3">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 mb-1">Passo 5</span>
                <h4 class="font-bold text-slate-800 text-sm">Entrega</h4>
                <p class="text-xs text-slate-500 mt-1">Conclusão do ciclo com comprovante e assinatura do recebedor.</p>
            </div>
        </div>
    </div>
</section>

<!-- Script para Preenchimento de Demonstração -->
<script>
    function preencherDemo() {
        document.getElementById('codigo_rastreio').value = 'VNX-BR782190';
        document.getElementById('documento').value = '123.456.789-01';
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
