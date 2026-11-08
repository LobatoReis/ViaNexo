<?php
/**
 * ViaNexo - Gestão Detalhada da Encomenda (Área do Gerente)
 * Regras:
 * 1. O gerente tem acesso vitalício às informações do ID
 * 2. Informações de gerenciamento da encomenda só com pessoal da transportadora
 * 3. Status homologados: Pedido Registrado, Em Preparação, Em Transporte, Em rota de entrega, Entrega
 */

require_once __DIR__ . '/includes/auth_guard.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    setToast('error', 'Identificador Inválido', 'A encomenda solicitada não foi encontrada.');
    redirect('dashboard.php');
}

// Busca completa dos dados com informações confidenciais da transportadora
$stmt = $pdo->prepare("
    SELECT 
        e.*,
        c.nome AS contratante_nome, c.email AS contratante_email, c.telefone AS contratante_telefone,
        c.tipo_documento, c.documento_mascarado, c.documento_hash, c.cidade AS contratante_cidade, c.estado AS contratante_uf
    FROM encomendas e
    INNER JOIN clientes c ON e.cliente_id = c.id
    WHERE e.id = :id
    LIMIT 1
");
$stmt->execute([':id' => $id]);
$encomenda = $stmt->fetch();

if (!$encomenda) {
    setModal('danger', 'Não Encontrado', 'O registro desta encomenda não foi localizado.');
    redirect('dashboard.php');
}

// Busca o histórico completo de movimentações
$stmtHist = $pdo->prepare("
    SELECT * FROM encomenda_historico
    WHERE encomenda_id = :id
    ORDER BY data_hora DESC, id DESC
");
$stmtHist->execute([':id' => $id]);
$historico = $stmtHist->fetchAll();

$pageTitle = "Gerenciamento Vitalício ID {$encomenda['codigo_rastreio']} - ViaNexo";
$activeNav = 'dashboard';
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Topbar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-1">
                <a href="dashboard.php" class="text-brand-600 hover:text-brand-800 flex items-center gap-1">
                    <i class="fa-solid fa-arrow-left"></i> Painel Geral
                </a>
                <span>•</span>
                <span class="text-emerald-600 font-bold flex items-center gap-1">
                    <i class="fa-solid fa-infinity"></i> Acesso Vitalício do Gerente
                </span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                    ID: <?= htmlspecialchars($encomenda['codigo_rastreio']) ?>
                </h1>
                <span class="px-3 py-1 rounded-full text-xs font-bold border <?= getStatusBadgeClass($encomenda['status_atual']) ?>">
                    <?= htmlspecialchars($encomenda['status_atual']) ?>
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">
                Contratante Emissor: <strong><?= htmlspecialchars($encomenda['contratante_nome']) ?></strong> (<?= htmlspecialchars($encomenda['documento_mascarado']) ?>)
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="rastreio.php?codigo=<?= $encomenda['codigo_rastreio'] ?>" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl shadow-sm transition-colors">
                <i class="fa-solid fa-arrow-up-right-from-square text-emerald-600"></i> Visualizar Área do Cliente
            </a>
            <button onclick="document.getElementById('modal-status').classList.remove('hidden'); document.getElementById('modal-status').classList.add('flex');" class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 active:bg-brand-800 text-white text-xs font-bold rounded-xl shadow-md shadow-brand-500/25 transition-all">
                <i class="fa-solid fa-route"></i> Atualizar Status & Rota
            </button>
        </div>
    </div>

    <!-- Grid de Informações -->
    <div class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Coluna Esquerda: Dados de Logística & Transporte (2 Colunas no Desktop) -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Card: Informações da Carga e Rota -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2 pb-3 border-b border-slate-100">
                    <i class="fa-solid fa-box text-brand-600"></i> Especificações da Carga e Rota
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                    <div class="p-3 bg-slate-50 rounded-xl">
                        <span class="text-slate-400 block mb-1">Descrição</span>
                        <strong class="text-slate-800"><?= htmlspecialchars($encomenda['descricao_pacote']) ?></strong>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-xl">
                        <span class="text-slate-400 block mb-1">Peso da Remessa</span>
                        <strong class="text-slate-800"><?= number_format($encomenda['peso_kg'], 2, ',', '.') ?> kg</strong>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-xl">
                        <span class="text-slate-400 block mb-1">Previsão de Entrega</span>
                        <strong class="text-slate-800"><?= formatDateOnlyBr($encomenda['previsao_entrega']) ?></strong>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs pt-2">
                    <div class="p-4 border border-blue-100 bg-blue-50/40 rounded-xl">
                        <span class="text-[10px] uppercase font-bold text-blue-600 block mb-1">Ponto de Origem</span>
                        <strong class="text-slate-900 text-sm"><?= htmlspecialchars($encomenda['origem_cidade']) ?> / <?= htmlspecialchars($encomenda['origem_uf']) ?></strong>
                    </div>
                    <div class="p-4 border border-emerald-100 bg-emerald-50/40 rounded-xl">
                        <span class="text-[10px] uppercase font-bold text-emerald-600 block mb-1">Ponto de Destino</span>
                        <strong class="text-slate-900 text-sm"><?= htmlspecialchars($encomenda['destino_cidade']) ?> / <?= htmlspecialchars($encomenda['destino_uf']) ?></strong>
                    </div>
                </div>

                <div class="p-3 bg-slate-50 rounded-xl text-xs">
                    <span class="text-slate-400 block mb-1">Endereço de Entrega Final</span>
                    <strong class="text-slate-800"><?= htmlspecialchars($encomenda['endereco_entrega']) ?></strong>
                </div>
            </div>

            <!-- Card: Informações Confidenciais da Transportadora (Restrito) -->
            <div class="bg-slate-900 text-white p-6 rounded-2xl shadow-xl space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                    <h2 class="text-sm font-bold text-white flex items-center gap-2">
                        <i class="fa-solid fa-lock text-brand-400"></i> Informações Restritas da Transportadora
                    </h2>
                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-brand-500/20 text-brand-300 border border-brand-500/30">
                        Acesso Gerencial
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                    <div class="p-3 bg-slate-800/80 rounded-xl border border-slate-700/60">
                        <span class="text-slate-400 block mb-1">Motorista Responsável</span>
                        <strong class="text-white"><?= htmlspecialchars($encomenda['motorista_nome'] ?: 'Não designado') ?></strong>
                    </div>
                    <div class="p-3 bg-slate-800/80 rounded-xl border border-slate-700/60">
                        <span class="text-slate-400 block mb-1">Placa do Veículo</span>
                        <strong class="text-white font-mono"><?= htmlspecialchars($encomenda['veiculo_placa'] ?: 'A definir') ?></strong>
                    </div>
                    <div class="p-3 bg-slate-800/80 rounded-xl border border-slate-700/60">
                        <span class="text-slate-400 block mb-1">Custo Operacional de Frete</span>
                        <strong class="text-emerald-400 text-sm font-bold">R$ <?= number_format($encomenda['custo_frete'], 2, ',', '.') ?></strong>
                    </div>
                </div>

                <div class="p-3 bg-slate-800/80 rounded-xl border border-slate-700/60 text-xs">
                    <span class="text-slate-400 block mb-1">Notas e Instruções Internas</span>
                    <p class="text-slate-300 leading-relaxed"><?= nl2br(htmlspecialchars($encomenda['observacoes_internas'] ?: 'Sem observações adicionais para este ID.')) ?></p>
                </div>
            </div>

            <!-- Timeline de Checkpoints da Encomenda -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-clock-rotate-left text-brand-600"></i> Histórico Vitalício de Movimentações (<?= count($historico) ?>)
                    </h2>
                    <button onclick="document.getElementById('modal-status').classList.remove('hidden'); document.getElementById('modal-status').classList.add('flex');" class="text-xs font-bold text-brand-600 hover:text-brand-800">
                        + Novo Evento
                    </button>
                </div>

                <div class="relative pl-6 space-y-6 before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                    <?php foreach ($historico as $index => $evento): 
                        $isFirst = $index === 0;
                        $dotClass = $isFirst ? 'bg-brand-600 ring-4 ring-brand-100' : 'bg-slate-300';
                    ?>
                        <div class="relative flex items-start group">
                            <div class="absolute -left-6 top-1 w-5 h-5 rounded-full <?= $dotClass ?> flex items-center justify-center text-[10px] text-white">
                                <?php if ($isFirst): ?>
                                    <i class="fa-solid fa-circle text-[6px]"></i>
                                <?php endif; ?>
                            </div>

                            <div class="flex-1 bg-slate-50 p-4 rounded-xl border border-slate-100 text-xs">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 mb-1">
                                    <span class="font-bold text-slate-900 flex items-center gap-2">
                                        <span class="px-2 py-0.5 rounded text-[10px] border <?= getStatusBadgeClass($evento['status']) ?>">
                                            <?= htmlspecialchars($evento['status']) ?>
                                        </span>
                                        <span><?= htmlspecialchars($evento['localizacao']) ?></span>
                                    </span>
                                    <time class="text-[11px] text-slate-400">
                                        <?= formatDateBr($evento['data_hora']) ?>
                                    </time>
                                </div>
                                <p class="text-slate-600 mt-1"><?= htmlspecialchars($evento['descricao_evento']) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>

        <!-- Coluna Direita: Contratante & Ações Rápidas -->
        <div class="space-y-6">
            
            <!-- Card do Contratante Emissor -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2 pb-3 border-b border-slate-100">
                    <i class="fa-solid fa-building-user text-brand-600"></i> Contratante Vinculado
                </h3>

                <div class="space-y-3 text-xs">
                    <div>
                        <span class="text-slate-400 block mb-0.5">Nome / Razão Social</span>
                        <strong class="text-slate-900 text-sm"><?= htmlspecialchars($encomenda['contratante_nome']) ?></strong>
                    </div>

                    <div>
                        <span class="text-slate-400 block mb-0.5">Documento Protegido (Hash Seguro)</span>
                        <div class="flex items-center gap-2">
                            <span class="font-mono bg-slate-100 px-2 py-1 rounded font-bold text-slate-700">
                                <?= htmlspecialchars($encomenda['documento_mascarado']) ?>
                            </span>
                            <span class="text-[10px] text-emerald-600 font-semibold bg-emerald-50 px-1.5 py-0.5 rounded">
                                HMAC Válido
                            </span>
                        </div>
                    </div>

                    <div>
                        <span class="text-slate-400 block mb-0.5">E-mail</span>
                        <span class="text-slate-700"><?= htmlspecialchars($encomenda['contratante_email'] ?: 'Não informado') ?></span>
                    </div>

                    <div>
                        <span class="text-slate-400 block mb-0.5">Telefone</span>
                        <span class="text-slate-700"><?= htmlspecialchars($encomenda['contratante_telefone'] ?: 'Não informado') ?></span>
                    </div>

                    <div>
                        <span class="text-slate-400 block mb-0.5">Sede</span>
                        <span class="text-slate-700"><?= htmlspecialchars($encomenda['contratante_cidade'] ?: '-') ?>/<?= htmlspecialchars($encomenda['contratante_uf'] ?: '-') ?></span>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100">
                    <span class="text-[10px] text-slate-400 block leading-tight">
                        O contratante utiliza este CPF/CNPJ para autenticação e consulta no portal público.
                    </span>
                </div>
            </div>

            <!-- Card: Atualização Rápida de Status -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2 pb-3 border-b border-slate-100">
                    <i class="fa-solid fa-bolt text-amber-500"></i> Atualização Rápida
                </h3>

                <p class="text-xs text-slate-500">
                    Avance a carga para a próxima etapa do ciclo de entrega.
                </p>

                <button type="button" 
                        onclick="document.getElementById('modal-status').classList.remove('hidden'); document.getElementById('modal-status').classList.add('flex');"
                        class="w-full flex items-center justify-center gap-2 py-3 px-4 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl shadow-sm transition-all">
                    <i class="fa-solid fa-location-arrow"></i> Despachar Novo Checkpoint
                </button>
            </div>

        </div>

    </div>

</div>

<!-- Modal para Atualizar Status & Localização -->
<div id="modal-status" class="hidden fixed inset-0 z-50 items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <div>
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-route text-brand-600"></i> Atualizar Status & Rastreamento
                </h3>
                <span class="text-xs text-slate-500 font-mono">ID: <?= $encomenda['codigo_rastreio'] ?></span>
            </div>
            <button type="button" onclick="document.getElementById('modal-status').classList.add('hidden'); document.getElementById('modal-status').classList.remove('flex');" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="actions/status_action.php" method="POST" class="space-y-4">
            <input type="hidden" name="encomenda_id" value="<?= $encomenda['id'] ?>">

            <div>
                <label for="status_atual" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Novo Status Homologado *
                </label>
                <select name="status_atual" id="status_atual" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <?php foreach (STATUS_ENCOMENDA as $st): ?>
                        <option value="<?= $st ?>" <?= $encomenda['status_atual'] === $st ? 'selected' : '' ?>>
                            <?= $st ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="localizacao_atual" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Nova Localização da Carga (Cidade/UF ou Posto) *
                </label>
                <input type="text" name="localizacao_atual" id="localizacao_atual" required value="<?= htmlspecialchars($encomenda['localizacao_atual']) ?>" placeholder="Ex: Centro de Distribuição - Cajamar/SP" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>

            <div>
                <label for="descricao_evento" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Descrição Pública do Evento (Exibida no rastreio)
                </label>
                <input type="text" name="descricao_evento" id="descricao_evento" placeholder="Ex: Carga despachada em veículo de transferência." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>

            <div class="pt-3 border-t border-slate-100">
                <span class="text-[11px] font-bold uppercase text-slate-400 block mb-2">Atualização Operacional (Opcional)</span>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-[10px] font-semibold text-slate-600">Motorista</label>
                        <input type="text" name="motorista_nome" value="<?= htmlspecialchars($encomenda['motorista_nome'] ?? '') ?>" placeholder="Nome do Motorista" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs">
                    </div>
                    <div>
                        <label class="text-[10px] font-semibold text-slate-600">Placa Veículo</label>
                        <input type="text" name="veiculo_placa" value="<?= htmlspecialchars($encomenda['veiculo_placa'] ?? '') ?>" placeholder="ABC-1234" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs uppercase">
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modal-status').classList.add('hidden'); document.getElementById('modal-status').classList.remove('flex');" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
                    Cancelar
                </button>
                <button type="submit" class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold rounded-xl shadow-sm transition-colors">
                    Salvar e Registrar Checkpoint
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
