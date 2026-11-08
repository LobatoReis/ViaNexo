<?php
/**
 * ViaNexo - Despacho e Criação de Encomendas
 * Regras:
 * 1. Toda encomenda tem ID (código de rastreio)
 * 2. O sistema não deverá permitir o cadastro de duas encomendas com o mesmo ID
 * 3. Informações de gerenciamento restritas à transportadora
 */

require_once __DIR__ . '/includes/auth_guard.php';

$pageTitle = 'Despachar Nova Encomenda - ViaNexo';
$activeNav = 'nova_encomenda';

// Gera sugestão de ID único
$codigoSugerido = generateTrackingCode($pdo);

// Busca lista de contratantes disponíveis
$clientes = $pdo->query("SELECT id, nome, documento_mascarado, tipo_documento FROM clientes ORDER BY nome ASC")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Topbar -->
    <div class="pb-6 border-b border-slate-200">
        <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-1">
            <a href="dashboard.php" class="text-brand-600 hover:text-brand-800">Painel Geral</a>
            <span>•</span>
            <span>Novo Registro de Despacho</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
            Despachar Nova Encomenda
        </h1>
        <p class="text-xs text-slate-500 mt-0.5">
            Geração de ID único e atribuição da carga ao contratante emissor.
        </p>
    </div>

    <?php if (empty($clientes)): ?>
        <div class="mt-8 bg-amber-50 border border-amber-200 rounded-2xl p-6 text-center">
            <div class="w-12 h-12 mx-auto rounded-full bg-amber-100 text-amber-600 flex items-center justify-center text-xl mb-3">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h3 class="text-sm font-bold text-amber-900">Nenhum Contratante Cadastrado</h3>
            <p class="text-xs text-amber-700 mt-1 max-w-md mx-auto">
                Para registrar uma encomenda e gerar o ID de rastreamento, você deve cadastrar previamente o contratante (com CPF/CNPJ seguro).
            </p>
            <div class="mt-4">
                <a href="clientes.php" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl shadow-sm transition-colors">
                    <i class="fa-solid fa-user-plus"></i> Cadastrar Contratante Agora
                </a>
            </div>
        </div>
    <?php else: ?>

        <form action="actions/encomenda_action.php" method="POST" class="mt-8 space-y-8">
            
            <!-- Bloco 1: Identificação & Contratante -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-fingerprint text-brand-600"></i> Identificação & Contratante
                    </h2>
                    <span class="text-[11px] font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                        ID Único Obrigatório
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Código / ID -->
                    <div>
                        <label for="codigo_rastreio" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            ID da Encomenda (Tracking Code) *
                        </label>
                        <div class="relative">
                            <input type="text" 
                                   name="codigo_rastreio" 
                                   id="codigo_rastreio" 
                                   value="<?= $codigoSugerido ?>" 
                                   required 
                                   oninput="formatTrackingInput(this)"
                                   class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 font-mono font-bold text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 uppercase">
                            <button type="button" 
                                    onclick="document.getElementById('codigo_rastreio').value = 'VNX-' + Math.random().toString(36).substring(2, 8).toUpperCase()" 
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-brand-600 hover:text-brand-800 text-xs font-semibold" 
                                    title="Gerar outro ID aleatório">
                                <i class="fa-solid fa-rotate mr-1"></i> Gerar Novo
                            </button>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">O sistema impede duplicidade de ID de forma estrita.</p>
                    </div>

                    <!-- Contratante Vinculado -->
                    <div>
                        <label for="cliente_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Contratante Emissor *
                        </label>
                        <select name="cliente_id" id="cliente_id" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-xs font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <option value="">Selecione o contratante...</option>
                            <?php foreach ($clientes as $cl): ?>
                                <option value="<?= $cl['id'] ?>">
                                    <?= htmlspecialchars($cl['nome']) ?> (<?= $cl['documento_mascarado'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="text-[11px] text-slate-400 mt-1">O contratante utilizará seu CPF/CNPJ para consultar este ID.</p>
                    </div>
                </div>

                <!-- Detalhes da Carga -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                    <div class="sm:col-span-2">
                        <label for="descricao_pacote" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Descrição do Pacote / Carga *
                        </label>
                        <input type="text" name="descricao_pacote" id="descricao_pacote" required placeholder="Ex: Equipamentos de informática, caixas frágeis" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label for="peso_kg" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Peso Estimado (kg)
                        </label>
                        <input type="number" step="0.01" name="peso_kg" id="peso_kg" placeholder="0.00" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>
            </div>

            <!-- Bloco 2: Origem, Destino e Previsão -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                <div class="pb-3 border-b border-slate-100">
                    <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-map-location-dot text-brand-600"></i> Rota & Localização do Pedido
                    </h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Origem -->
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/60 space-y-3">
                        <span class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-dot text-blue-500"></i> Ponto de Origem
                        </span>
                        <div class="grid grid-cols-3 gap-2">
                            <div class="col-span-2">
                                <label class="text-[10px] font-semibold text-slate-500">Cidade *</label>
                                <input type="text" name="origem_cidade" required placeholder="São Paulo" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs">
                            </div>
                            <div>
                                <label class="text-[10px] font-semibold text-slate-500">UF *</label>
                                <input type="text" name="origem_uf" required maxlength="2" placeholder="SP" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs uppercase">
                            </div>
                        </div>
                    </div>

                    <!-- Destino -->
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/60 space-y-3">
                        <span class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="fa-solid fa-location-dot text-emerald-500"></i> Ponto de Destino
                        </span>
                        <div class="grid grid-cols-3 gap-2">
                            <div class="col-span-2">
                                <label class="text-[10px] font-semibold text-slate-500">Cidade *</label>
                                <input type="text" name="destino_cidade" required placeholder="Curitiba" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs">
                            </div>
                            <div>
                                <label class="text-[10px] font-semibold text-slate-500">UF *</label>
                                <input type="text" name="destino_uf" required maxlength="2" placeholder="PR" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs uppercase">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Endereço Completo -->
                <div>
                    <label for="endereco_entrega" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Endereço Completo de Entrega (Rua, Número, Bairro, CEP) *
                    </label>
                    <input type="text" name="endereco_entrega" id="endereco_entrega" required placeholder="Ex: Av. das Nações Unidas, 1200 - Galpão 4, CEP: 04578-000" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <!-- Status Inicial & Localização -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                    <div>
                        <label for="status_atual" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Status Inicial *
                        </label>
                        <select name="status_atual" id="status_atual" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <?php foreach (STATUS_ENCOMENDA as $st): ?>
                                <option value="<?= $st ?>" <?= $st === 'Pedido Registrado' ? 'selected' : '' ?>>
                                    <?= $st ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="localizacao_atual" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Localização Inicial da Carga
                        </label>
                        <input type="text" name="localizacao_atual" id="localizacao_atual" placeholder="Centro de Triagem Logística" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>

                    <div>
                        <label for="previsao_entrega" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Previsão de Entrega
                        </label>
                        <input type="date" name="previsao_entrega" id="previsao_entrega" value="<?= date('Y-m-d', strtotime('+3 days')) ?>" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>
            </div>

            <!-- Bloco 3: Informações de Gerenciamento Restritas da Transportadora -->
            <div class="bg-slate-900 text-white p-6 rounded-2xl shadow-xl space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                    <div>
                        <h2 class="text-sm font-bold text-white flex items-center gap-2">
                            <i class="fa-solid fa-lock text-brand-400"></i> Informações de Gestão Interna (Exclusivo Transportadora)
                        </h2>
                        <p class="text-[11px] text-slate-400 mt-0.5">
                            Dados confidenciais não expostos ao cliente na tela de rastreamento.
                        </p>
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded bg-brand-500/20 text-brand-300 border border-brand-500/30">
                        Sigiloso
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="motorista_nome" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                            Motorista Designado
                        </label>
                        <input type="text" name="motorista_nome" id="motorista_nome" placeholder="Ex: Roberto Silva" class="w-full px-4 py-2.5 bg-slate-800 border border-slate-700 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>

                    <div>
                        <label for="veiculo_placa" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                            Placa do Veículo
                        </label>
                        <input type="text" name="veiculo_placa" id="veiculo_placa" placeholder="ABC-1D23" class="w-full px-4 py-2.5 bg-slate-800 border border-slate-700 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500 uppercase">
                    </div>

                    <div>
                        <label for="custo_frete" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                            Custo Operacional do Frete (R$)
                        </label>
                        <input type="number" step="0.01" name="custo_frete" id="custo_frete" placeholder="0.00" class="w-full px-4 py-2.5 bg-slate-800 border border-slate-700 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>

                <div>
                    <label for="observacoes_internas" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                        Observações Internas de Logística / Despacho
                    </label>
                    <textarea name="observacoes_internas" id="observacoes_internas" rows="2" placeholder="Instruções de doca, seguro, manifesto de carga ou manuseio..." class="w-full px-4 py-2.5 bg-slate-800 border border-slate-700 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500"></textarea>
                </div>
            </div>

            <!-- Botões de Ação -->
            <div class="flex flex-col sm:flex-row items-center justify-end gap-3 pt-4">
                <a href="dashboard.php" class="w-full sm:w-auto px-6 py-3 text-center text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
                    Cancelar
                </a>
                <button type="submit" class="w-full sm:w-auto flex items-center justify-center gap-2 px-8 py-3.5 bg-brand-600 hover:bg-brand-700 active:bg-brand-800 text-white text-xs font-bold rounded-xl shadow-lg shadow-brand-500/25 transition-all">
                    <i class="fa-solid fa-truck-ramp-box"></i> Registrar e Emitir ID da Carga
                </button>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
