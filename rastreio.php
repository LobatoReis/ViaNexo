<?php
/**
 * ViaNexo - Área Restrita de Rastreamento (Visão do Cliente / Contratante)
 * Regras:
 * 1. O usuário deve inserir ID e CPF para acessar esta área restrita
 * 2. Informações de gerenciamento da encomenda só com pessoal da transportadora
 * 3. Cliente visualiza apenas status da encomenda e linha do tempo de localização
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/config/bd.php';

$codigo = strtoupper(trim($_GET['codigo'] ?? ''));

// Proteção da Área Restrita do Cliente
$isGerente = isset($_SESSION['gerente_id']) && !empty($_SESSION['gerente_id']);
$isAuthorizedClient = isset($_SESSION['tracking_authorized_codigo']) && $_SESSION['tracking_authorized_codigo'] === $codigo;

if (!$isGerente && !$isAuthorizedClient) {
    setModal('warning', 'Área Restrita', 'Para acessar os detalhes desta encomenda, forneça o ID e o CPF/CNPJ correspondente na página de rastreio.');
    redirect('index.php');
}

// Busca a encomenda e dados públicos do contratante
$stmt = $pdo->prepare("
    SELECT 
        e.id, e.codigo_rastreio, e.descricao_pacote, e.peso_kg,
        e.origem_cidade, e.origem_uf, e.destino_cidade, e.destino_uf,
        e.endereco_entrega, e.status_atual, e.localizacao_atual, e.previsao_entrega,
        e.created_at,
        c.nome AS contratante_nome, c.documento_mascarado
    FROM encomendas e
    INNER JOIN clientes c ON e.cliente_id = c.id
    WHERE UPPER(e.codigo_rastreio) = :codigo
    LIMIT 1
");
$stmt->execute([':codigo' => $codigo]);
$encomenda = $stmt->fetch();

if (!$encomenda) {
    setModal('danger', 'ID Inexistente', "A encomenda com ID {$codigo} não foi localizada no banco de dados.");
    redirect('index.php');
}

// Busca o histórico de movimentações públicas
$stmtHist = $pdo->prepare("
    SELECT status, localizacao, descricao_evento, data_hora
    FROM encomenda_historico
    WHERE encomenda_id = :id
    ORDER BY data_hora DESC, id DESC
");
$stmtHist->execute([':id' => $encomenda['id']]);
$historico = $stmtHist->fetchAll();

$currentStepIndex = getStatusStepIndex($encomenda['status_atual']);
$pageTitle = "Rastreio {$encomenda['codigo_rastreio']} - ViaNexo";
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
    
    <!-- Cabeçalho do Rastreio -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2">
                <a href="index.php" class="text-xs font-semibold text-brand-600 hover:text-brand-800 flex items-center gap-1">
                    <i class="fa-solid fa-arrow-left"></i> Nova Consulta
                </a>
                <span class="text-slate-300">•</span>
                <span class="text-xs font-semibold text-slate-500">Área Restrita do Cliente</span>
            </div>
            <div class="flex items-center gap-3 mt-1.5">
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                    ID: <?= htmlspecialchars($encomenda['codigo_rastreio']) ?>
                </h1>
                <span class="px-3 py-1 rounded-full text-xs font-bold border <?= getStatusBadgeClass($encomenda['status_atual']) ?>">
                    <?= htmlspecialchars($encomenda['status_atual']) ?>
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">
                Contratante: <strong class="text-slate-700"><?= htmlspecialchars($encomenda['contratante_nome']) ?></strong> (<?= htmlspecialchars($encomenda['documento_mascarado']) ?>)
            </p>
        </div>

        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 shadow-sm transition-colors">
                <i class="fa-solid fa-print"></i> Imprimir Rastreio
            </button>
            <?php if ($isGerente): ?>
                <a href="detalhes.php?id=<?= $encomenda['id'] ?>" class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-xl shadow-sm transition-colors">
                    <i class="fa-solid fa-sliders"></i> Gerenciar Logística
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Stepper Visual Interativo de Status (Mobile-First) -->
    <div class="mt-8 bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-6">
            Progresso da Entrega (Status Atual: <?= htmlspecialchars($encomenda['status_atual']) ?>)
        </h2>

        <!-- Linha do Stepper para Desktop e Mobile -->
        <div class="relative">
            <!-- Barra de Progresso de Fundo -->
            <div class="hidden sm:block absolute top-5 left-6 right-6 h-1 bg-slate-100 rounded-full"></div>
            <!-- Barra de Progresso Ativa -->
            <?php 
                $progressPercentage = ($currentStepIndex / (count(STATUS_ENCOMENDA) - 1)) * 100;
            ?>
            <div class="hidden sm:block absolute top-5 left-6 h-1 bg-brand-600 rounded-full transition-all duration-500" style="width: calc(<?= $progressPercentage ?>% - 3rem);"></div>

            <!-- Etapas -->
            <div class="grid grid-cols-1 sm:grid-cols-5 gap-4 relative z-10">
                <?php foreach (STATUS_ENCOMENDA as $idx => $stName): 
                    $isCompleted = $idx < $currentStepIndex;
                    $isCurrent = $idx === $currentStepIndex;
                    
                    $icon = match($stName) {
                        'Pedido Registrado'  => 'fa-file-invoice',
                        'Em Preparação'     => 'fa-boxes-packing',
                        'Em Transporte'      => 'fa-truck-arrow-right',
                        'Em rota de entrega' => 'fa-motorcycle',
                        'Entrega'            => 'fa-house-circle-check',
                        default              => 'fa-box'
                    };

                    $circleClass = $isCurrent 
                        ? 'bg-brand-600 text-white ring-4 ring-brand-100 shadow-lg shadow-brand-500/30' 
                        : ($isCompleted 
                            ? 'bg-emerald-500 text-white' 
                            : 'bg-slate-100 text-slate-400');
                ?>
                    <div class="flex sm:flex-col items-center gap-3 sm:gap-2 sm:text-center">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 font-bold text-sm transition-all <?= $circleClass ?>">
                            <?php if ($isCompleted): ?>
                                <i class="fa-solid fa-check"></i>
                            <?php else: ?>
                                <i class="fa-solid <?= $icon ?>"></i>
                            <?php endif; ?>
                        </div>
                        <div>
                            <p class="text-xs font-bold <?= $isCurrent ? 'text-brand-600' : ($isCompleted ? 'text-slate-800' : 'text-slate-400') ?>">
                                <?= htmlspecialchars($stName) ?>
                            </p>
                            <span class="text-[10px] text-slate-400 block">
                                <?= $isCurrent ? 'Etapa Atual' : ($isCompleted ? 'Concluído' : 'Pendente') ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Alerta de Localização Atual -->
        <div class="mt-6 pt-5 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs bg-slate-50 p-4 rounded-xl">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-brand-500 animate-ping"></span>
                <span class="text-slate-500 font-medium">Última Localização Registrada:</span>
                <strong class="text-slate-900"><?= htmlspecialchars($encomenda['localizacao_atual']) ?></strong>
            </div>
            <div class="text-slate-500">
                <span>Previsão de Entrega:</span>
                <strong class="text-slate-900 ml-1"><?= formatDateOnlyBr($encomenda['previsao_entrega']) ?></strong>
            </div>
        </div>
    </div>

    <!-- Informações do Envio e Destino -->
    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Card: Dados do Pacote Permitidos ao Cliente -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
            <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-box text-brand-600"></i> Informações do Pacote
            </h3>
            <div class="space-y-3 text-xs">
                <div class="flex justify-between py-1.5 border-b border-slate-100">
                    <span class="text-slate-500">Conteúdo / Descrição</span>
                    <strong class="text-slate-800 text-right"><?= htmlspecialchars($encomenda['descricao_pacote']) ?></strong>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-100">
                    <span class="text-slate-500">Peso Estimado</span>
                    <strong class="text-slate-800"><?= number_format($encomenda['peso_kg'], 2, ',', '.') ?> kg</strong>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-100">
                    <span class="text-slate-500">Origem da Carga</span>
                    <strong class="text-slate-800"><?= htmlspecialchars($encomenda['origem_cidade']) ?>/<?= htmlspecialchars($encomenda['origem_uf']) ?></strong>
                </div>
                <div class="flex justify-between py-1.5">
                    <span class="text-slate-500">Data de Envio</span>
                    <strong class="text-slate-800"><?= formatDateBr($encomenda['created_at']) ?></strong>
                </div>
            </div>
        </div>

        <!-- Card: Destino e Entrega -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
            <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-location-dot text-brand-600"></i> Destino da Remessa
            </h3>
            <div class="space-y-3 text-xs">
                <div class="flex justify-between py-1.5 border-b border-slate-100">
                    <span class="text-slate-500">Cidade de Destino</span>
                    <strong class="text-slate-800"><?= htmlspecialchars($encomenda['destino_cidade']) ?>/<?= htmlspecialchars($encomenda['destino_uf']) ?></strong>
                </div>
                <div class="py-1.5 border-b border-slate-100">
                    <span class="text-slate-500 block mb-1">Endereço de Entrega</span>
                    <strong class="text-slate-800"><?= htmlspecialchars($encomenda['endereco_entrega']) ?></strong>
                </div>
                <div class="flex justify-between py-1.5">
                    <span class="text-slate-500">Status de Liberação</span>
                    <span class="text-emerald-600 font-bold flex items-center gap-1">
                        <i class="fa-solid fa-circle-check"></i> Rota Autorizada
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Linha do Tempo de Rastreamento (Histórico de Checkpoints) -->
    <div class="mt-8 bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
        <h3 class="text-sm font-bold text-slate-900 mb-6 flex items-center gap-2">
            <i class="fa-solid fa-timeline text-brand-600"></i> Histórico de Movimentações
        </h3>

        <?php if (empty($historico)): ?>
            <p class="text-xs text-slate-400 text-center py-6">Nenhum evento registrado até o momento.</p>
        <?php else: ?>
            <div class="relative pl-6 space-y-8 before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                <?php foreach ($historico as $index => $evento): 
                    $isFirst = $index === 0;
                    $dotClass = $isFirst ? 'bg-brand-600 ring-4 ring-brand-100' : 'bg-slate-300';
                ?>
                    <div class="relative flex items-start group">
                        <!-- Ponto na linha do tempo -->
                        <div class="absolute -left-6 top-1 w-5 h-5 rounded-full <?= $dotClass ?> flex items-center justify-center text-[10px] text-white">
                            <?php if ($isFirst): ?>
                                <i class="fa-solid fa-circle text-[6px]"></i>
                            <?php endif; ?>
                        </div>

                        <!-- Conteúdo do evento -->
                        <div class="flex-1 bg-slate-50 hover:bg-slate-100/80 transition-colors p-4 rounded-xl border border-slate-100">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 mb-1">
                                <span class="text-xs font-bold text-slate-900 flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded text-[10px] border <?= getStatusBadgeClass($evento['status']) ?>">
                                        <?= htmlspecialchars($evento['status']) ?>
                                    </span>
                                    <span><?= htmlspecialchars($evento['localizacao']) ?></span>
                                </span>
                                <time class="text-[11px] text-slate-400 font-medium">
                                    <i class="fa-regular fa-clock mr-1"></i> <?= formatDateBr($evento['data_hora']) ?>
                                </time>
                            </div>
                            <p class="text-xs text-slate-600 mt-1">
                                <?= htmlspecialchars($evento['descricao_evento']) ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Nota de Privacidade LGPD / Regra da Transportadora -->
    <div class="mt-6 p-4 rounded-xl bg-slate-100/70 text-[11px] text-slate-500 flex items-start gap-3">
        <i class="fa-solid fa-lock text-slate-400 mt-0.5"></i>
        <div>
            <strong class="text-slate-700">Privacidade Garantida:</strong>
            Conforme as diretrizes de segurança da ViaNexo, detalhes operacionais de motorista, placa e custos de transporte são confidenciais e gerenciados exclusivamente pela transportadora. O cliente visualiza apenas o status e a rota de entrega.
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
