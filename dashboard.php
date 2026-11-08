<?php
/**
 * ViaNexo - Dashboard do Gerente (Transportadora)
 * Regras:
 * 1. O gerente tem acesso vitalício às informações do ID
 * 2. Visualização completa de métricas, status e logística
 */

require_once __DIR__ . '/includes/auth_guard.php';

$gerente = getCurrentGerente($pdo);
$pageTitle = 'Painel Logístico do Gerente - ViaNexo';
$activeNav = 'dashboard';

// Filtros de busca
$filtroStatus = trim($_GET['status'] ?? '');
$buscaId = trim($_GET['busca'] ?? '');

// Consulta de KPIs / Métricas
$totalEncomendas = $pdo->query("SELECT COUNT(*) FROM encomendas")->fetchColumn();
$totalTransito   = $pdo->query("SELECT COUNT(*) FROM encomendas WHERE status_atual IN ('Em Transporte', 'Em Preparação')")->fetchColumn();
$totalRota       = $pdo->query("SELECT COUNT(*) FROM encomendas WHERE status_atual = 'Em rota de entrega'")->fetchColumn();
$totalEntregues  = $pdo->query("SELECT COUNT(*) FROM encomendas WHERE status_atual = 'Entrega'")->fetchColumn();
$totalClientes   = $pdo->query("SELECT COUNT(*) FROM clientes")->fetchColumn();

// Montagem da query de listagem com filtros
$sql = "
    SELECT 
        e.id, e.codigo_rastreio, e.descricao_pacote, e.peso_kg,
        e.origem_cidade, e.origem_uf, e.destino_cidade, e.destino_uf,
        e.status_atual, e.localizacao_atual, e.created_at, e.previsao_entrega,
        e.motorista_nome, e.veiculo_placa,
        c.nome AS contratante_nome, c.documento_mascarado
    FROM encomendas e
    INNER JOIN clientes c ON e.cliente_id = c.id
    WHERE 1=1
";
$params = [];

if (!empty($filtroStatus)) {
    $sql .= " AND e.status_atual = :status";
    $params[':status'] = $filtroStatus;
}

if (!empty($buscaId)) {
    $sql .= " AND (UPPER(e.codigo_rastreio) LIKE :busca OR UPPER(c.nome) LIKE :busca)";
    $params[':busca'] = '%' . strtoupper($buscaId) . '%';
}

$sql .= " ORDER BY e.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$encomendas = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Topbar do Painel -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-1">
                <span>Terminal Operacional</span>
                <span>•</span>
                <span class="text-emerald-600 font-bold flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Acesso Vitalício do Gerente
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                Painel de Gestão Logística
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Monitoramento completo de remessas, transportes e contratantes geradores de ID.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="clientes.php" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl shadow-sm transition-colors">
                <i class="fa-solid fa-building-user text-slate-500"></i> Contratantes (<?= $totalClientes ?>)
            </a>
            <a href="nova_encomenda.php" class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 active:bg-brand-800 text-white text-xs font-bold rounded-xl shadow-md shadow-brand-500/25 transition-all">
                <i class="fa-solid fa-plus-circle"></i> Despachar Nova Encomenda
            </a>
        </div>
    </div>

    <!-- Cards de Métricas (KPIs) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
        <!-- KPI 1 -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
            <div>
                <span class="text-xs font-medium text-slate-500 block">Total de Remessas</span>
                <span class="text-2xl font-black text-slate-900"><?= $totalEncomendas ?></span>
            </div>
        </div>

        <!-- KPI 2 -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-truck-moving"></i>
            </div>
            <div>
                <span class="text-xs font-medium text-slate-500 block">Em Trânsito / Prep.</span>
                <span class="text-2xl font-black text-slate-900"><?= $totalTransito ?></span>
            </div>
        </div>

        <!-- KPI 3 -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-motorcycle"></i>
            </div>
            <div>
                <span class="text-xs font-medium text-slate-500 block">Em Rota de Entrega</span>
                <span class="text-2xl font-black text-slate-900"><?= $totalRota ?></span>
            </div>
        </div>

        <!-- KPI 4 -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <span class="text-xs font-medium text-slate-500 block">Entregas Concluídas</span>
                <span class="text-2xl font-black text-slate-900"><?= $totalEntregues ?></span>
            </div>
        </div>
    </div>

    <!-- Barra de Filtros e Busca -->
    <div class="mt-8 bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
        <form method="GET" action="dashboard.php" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <input type="text" 
                       name="busca" 
                       value="<?= htmlspecialchars($buscaId) ?>"
                       placeholder="Buscar por ID de rastreio ou nome do contratante..." 
                       class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 transition-all">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </span>
            </div>

            <div class="w-full sm:w-60">
                <select name="status" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 transition-all font-medium">
                    <option value="">Todos os Status</option>
                    <?php foreach (STATUS_ENCOMENDA as $st): ?>
                        <option value="<?= $st ?>" <?= $filtroStatus === $st ? 'selected' : '' ?>>
                            <?= $st ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button type="submit" class="flex-1 sm:flex-none px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl shadow-sm transition-colors">
                    Filtrar
                </button>
                <?php if (!empty($buscaId) || !empty($filtroStatus)): ?>
                    <a href="dashboard.php" class="px-3 py-2.5 text-xs text-slate-500 hover:text-slate-800 border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors" title="Limpar Filtros">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Tabela Responsiva de Encomendas -->
    <div class="mt-6 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-list-check text-brand-600"></i> Encomendas Ativas (<?= count($encomendas) ?>)
            </h2>
            <span class="text-xs text-slate-400">Clique no ID para gerenciar detalhes vitais</span>
        </div>

        <?php if (empty($encomendas)): ?>
            <div class="text-center py-12 px-4">
                <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-slate-400 text-xl mb-3">
                    <i class="fa-solid fa-box-open"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-800">Nenhuma encomenda localizada</h3>
                <p class="text-xs text-slate-500 mt-1">Não encontramos cargas correspondentes aos filtros aplicados.</p>
                <div class="mt-4">
                    <a href="nova_encomenda.php" class="inline-flex items-center gap-1.5 px-4 py-2 bg-brand-600 text-white text-xs font-bold rounded-xl shadow-sm hover:bg-brand-700">
                        <i class="fa-solid fa-plus"></i> Despachar Agora
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase tracking-wider text-[11px] font-bold">
                            <th class="py-3.5 px-6">ID da Encomenda</th>
                            <th class="py-3.5 px-6">Contratante</th>
                            <th class="py-3.5 px-6">Rota (Origem &rarr; Destino)</th>
                            <th class="py-3.5 px-6">Status Atual</th>
                            <th class="py-3.5 px-6">Localização Atual</th>
                            <th class="py-3.5 px-6">Previsão</th>
                            <th class="py-3.5 px-6 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($encomendas as $enc): ?>
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-4 px-6 font-mono font-bold text-slate-900">
                                    <a href="detalhes.php?id=<?= $enc['id'] ?>" class="text-brand-600 hover:text-brand-800 flex items-center gap-1.5">
                                        <i class="fa-solid fa-barcode text-xs opacity-70"></i>
                                        <?= htmlspecialchars($enc['codigo_rastreio']) ?>
                                    </a>
                                    <span class="text-[10px] text-slate-400 font-sans block mt-0.5 truncate max-w-[160px]">
                                        <?= htmlspecialchars($enc['descricao_pacote']) ?>
                                    </span>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="font-bold text-slate-800 block"><?= htmlspecialchars($enc['contratante_nome']) ?></span>
                                    <span class="text-[11px] text-slate-400 font-mono"><?= htmlspecialchars($enc['documento_mascarado']) ?></span>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-1 text-slate-700 font-medium">
                                        <span><?= htmlspecialchars($enc['origem_cidade']) ?>/<?= $enc['origem_uf'] ?></span>
                                        <i class="fa-solid fa-arrow-right text-[10px] text-slate-400 mx-1"></i>
                                        <span><?= htmlspecialchars($enc['destino_cidade']) ?>/<?= $enc['destino_uf'] ?></span>
                                    </div>
                                    <span class="text-[10px] text-slate-400 block mt-0.5"><?= number_format($enc['peso_kg'], 2, ',', '.') ?> kg</span>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold border <?= getStatusBadgeClass($enc['status_atual']) ?>">
                                        <?= htmlspecialchars($enc['status_atual']) ?>
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-slate-600">
                                    <span class="font-medium text-slate-800 block truncate max-w-[180px]"><?= htmlspecialchars($enc['localizacao_atual']) ?></span>
                                    <?php if (!empty($enc['motorista_nome'])): ?>
                                        <span class="text-[10px] text-slate-400 block">Motorista: <?= htmlspecialchars($enc['motorista_nome']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-4 px-6 text-slate-600 whitespace-nowrap">
                                    <?= formatDateOnlyBr($enc['previsao_entrega']) ?>
                                </td>
                                <td class="py-4 px-6 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5">
                                        <a href="detalhes.php?id=<?= $enc['id'] ?>" class="p-2 text-slate-600 hover:text-brand-600 hover:bg-slate-100 rounded-lg transition-colors" title="Gerenciamento Interno do Gerente">
                                            <i class="fa-solid fa-pen-to-square text-sm"></i>
                                        </a>
                                        <a href="rastreio.php?codigo=<?= $enc['codigo_rastreio'] ?>" target="_blank" class="p-2 text-slate-600 hover:text-emerald-600 hover:bg-slate-100 rounded-lg transition-colors" title="Ver Visão do Cliente">
                                            <i class="fa-solid fa-arrow-up-right-from-square text-sm"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
