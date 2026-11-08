<?php
/**
 * ViaNexo - Gestão de Contratantes (Clientes)
 * Regras:
 * 1. O cliente não pode registrar o mesmo CPF/CNPJ 2 vezes
 * 2. O CNPJ/CPF deve ser armazenado com Hash Seguro (HMAC-SHA256 Blind Index)
 */

require_once __DIR__ . '/includes/auth_guard.php';

$pageTitle = 'Contratantes & Clientes - ViaNexo';
$activeNav = 'clientes';

// Busca lista de clientes cadastrados com contagem de encomendas
$sql = "
    SELECT 
        c.id, c.nome, c.email, c.telefone, c.tipo_documento,
        c.documento_hash, c.documento_mascarado, c.cidade, c.estado, c.created_at,
        COUNT(e.id) AS total_encomendas
    FROM clientes c
    LEFT JOIN encomendas e ON c.id = e.cliente_id
    GROUP BY c.id
    ORDER BY c.created_at DESC
";
$clientes = $pdo->query($sql)->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Topbar & Ação de Cadastro -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-1">
                <span>Cadastro de Contratantes</span>
                <span>•</span>
                <span class="text-brand-600 font-bold flex items-center gap-1">
                    <i class="fa-solid fa-fingerprint"></i> Hash Seguro Ativo
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                Contratantes de Cargas
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Emissão de ID vinculada ao contratante com validação de unicidade de CPF/CNPJ.
            </p>
        </div>

        <div>
            <button type="button" 
                    onclick="document.getElementById('modal-novo-cliente').classList.remove('hidden'); document.getElementById('modal-novo-cliente').classList.add('flex');"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 active:bg-brand-800 text-white text-xs font-bold rounded-xl shadow-md shadow-brand-500/25 transition-all">
                <i class="fa-solid fa-user-plus"></i> Novo Contratante
            </button>
        </div>
    </div>

    <!-- Regras de Compliance / Blind Index Info Banner -->
    <div class="mt-6 p-4 rounded-2xl bg-indigo-50 border border-indigo-100 flex flex-col sm:flex-row items-start gap-4">
        <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center flex-shrink-0 text-lg">
            <i class="fa-solid fa-shield-halved"></i>
        </div>
        <div class="text-xs text-indigo-900 leading-relaxed">
            <strong class="font-bold text-indigo-950 block text-sm">Diretriz de Segurança do Sistema ViaNexo:</strong>
            1. <strong>Unicidade Estrita:</strong> O sistema bloqueia duplicidades. O mesmo CPF/CNPJ não pode ser cadastrado duas vezes.<br>
            2. <strong>Armazenamento com Hash Seguro:</strong> Documentos de clientes são convertidos em <em>Blind Index (HMAC-SHA256)</em>, permitindo localização segura do pedido sem jamais manter o número puro visível no banco de dados.
        </div>
    </div>

    <!-- Tabela de Contratantes -->
    <div class="mt-6 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-users text-brand-600"></i> Contratantes Cadastrados (<?= count($clientes) ?>)
            </h2>
        </div>

        <?php if (empty($clientes)): ?>
            <div class="text-center py-12 px-4">
                <p class="text-xs text-slate-500">Nenhum contratante cadastrado até o momento.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase tracking-wider text-[11px] font-bold">
                            <th class="py-3.5 px-6">Contratante / Razão Social</th>
                            <th class="py-3.5 px-6">Documento Mascarado</th>
                            <th class="py-3.5 px-6">Blind Index (Hash Seguro)</th>
                            <th class="py-3.5 px-6">Contato</th>
                            <th class="py-3.5 px-6">Localidade</th>
                            <th class="py-3.5 px-6 text-center">Encomendas</th>
                            <th class="py-3.5 px-6 text-right">Data Cadastro</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($clientes as $c): ?>
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-4 px-6 font-bold text-slate-900">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-600 font-bold text-xs">
                                            <?= strtoupper(substr($c['nome'], 0, 2)) ?>
                                        </div>
                                        <span><?= htmlspecialchars($c['nome']) ?></span>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded bg-slate-100 text-slate-700 font-mono text-[11px] font-bold">
                                        <span class="text-[10px] text-slate-400"><?= $c['tipo_documento'] ?>:</span>
                                        <?= htmlspecialchars($c['documento_mascarado']) ?>
                                    </span>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="font-mono text-[10px] text-indigo-700 bg-indigo-50 border border-indigo-200 px-2 py-0.5 rounded" title="HMAC-SHA256: <?= $c['documento_hash'] ?>">
                                        <?= substr($c['documento_hash'], 0, 16) ?>...
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-slate-600">
                                    <span><?= htmlspecialchars($c['email'] ?: 'Sem e-mail') ?></span>
                                    <span class="text-[10px] text-slate-400 block"><?= htmlspecialchars($c['telefone'] ?: '-') ?></span>
                                </td>
                                <td class="py-4 px-6 text-slate-600">
                                    <?= htmlspecialchars($c['cidade'] ?: '-') ?><?= $c['estado'] ? '/' . $c['estado'] : '' ?>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold <?= $c['total_encomendas'] > 0 ? 'bg-brand-50 text-brand-700' : 'bg-slate-100 text-slate-500' ?>">
                                        <?= $c['total_encomendas'] ?> ID(s)
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-right text-slate-500 whitespace-nowrap">
                                    <?= formatDateOnlyBr($c['created_at']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para Cadastro de Novo Contratante -->
<div id="modal-novo-cliente" class="hidden fixed inset-0 z-50 items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 transform transition-all">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-user-plus text-brand-600"></i> Cadastrar Novo Contratante
            </h3>
            <button type="button" onclick="document.getElementById('modal-novo-cliente').classList.add('hidden'); document.getElementById('modal-novo-cliente').classList.remove('flex');" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="actions/cliente_action.php" method="POST" class="space-y-4">
            <input type="hidden" name="action" value="create">

            <div>
                <label for="nome" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                    Nome Completo / Razão Social *
                </label>
                <input type="text" name="nome" id="nome" required placeholder="Ex: Logística Express LTDA" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="tipo_documento" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Tipo de Documento *
                    </label>
                    <select name="tipo_documento" id="tipo_documento" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 font-medium">
                        <option value="CPF">CPF (Pessoa Física)</option>
                        <option value="CNPJ">CNPJ (Pessoa Jurídica)</option>
                    </select>
                </div>
                <div>
                    <label for="documento_novo" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Número do Documento *
                    </label>
                    <input type="text" name="documento" id="documento_novo" required placeholder="000.000.000-00" oninput="maskCpfCnpj(this)" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 font-mono">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        E-mail
                    </label>
                    <input type="email" name="email" id="email" placeholder="cliente@email.com" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label for="telefone" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Telefone / WhatsApp
                    </label>
                    <input type="text" name="telefone" id="telefone" placeholder="(11) 98765-4321" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div class="col-span-2">
                    <label for="cidade" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Cidade
                    </label>
                    <input type="text" name="cidade" id="cidade" placeholder="São Paulo" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label for="estado" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        UF
                    </label>
                    <input type="text" name="estado" id="estado" maxlength="2" placeholder="SP" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 uppercase focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
            </div>

            <div class="p-3 bg-slate-50 rounded-xl text-[11px] text-slate-500">
                <i class="fa-solid fa-lock text-brand-600 mr-1"></i> O CPF/CNPJ será criptografado via hash HMAC-SHA256 imediatamente. O sistema não permitirá cadastros duplicados deste documento.
            </div>

            <div class="mt-6 flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modal-novo-cliente').classList.add('hidden'); document.getElementById('modal-novo-cliente').classList.remove('flex');" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
                    Cancelar
                </button>
                <button type="submit" class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold rounded-xl shadow-sm transition-colors">
                    Salvar Contratante
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
