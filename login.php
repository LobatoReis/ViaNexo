<?php
/**
 * ViaNexo - Login da Transportadora (Área do Gerente)
 * Regras: Trate sempre as credenciais com Bcrypt (password_verify)
 */

$pageTitle = 'Login do Gerente - ViaNexo';
require_once __DIR__ . '/includes/header.php';

// Se já estiver logado, redireciona para o dashboard
if (isset($_SESSION['gerente_id'])) {
    redirect('dashboard.php');
}
?>

<div class="min-h-[calc(100vh-14rem)] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-8 sm:p-10 rounded-2xl border border-slate-200 shadow-xl">
        
        <div class="text-center">
            <div class="w-14 h-14 mx-auto rounded-2xl bg-gradient-to-tr from-brand-700 to-indigo-500 text-white flex items-center justify-center text-2xl shadow-lg shadow-brand-500/25">
                <i class="fa-solid fa-user-shield"></i>
            </div>
            <h2 class="mt-4 text-2xl font-black text-slate-900 tracking-tight">
                Acesso do Gerente
            </h2>
            <p class="mt-1 text-xs text-slate-500">
                Acesso vitalício ao gerenciamento de IDs e logística da transportadora
            </p>
        </div>

        <form class="mt-6 space-y-4" action="actions/login_action.php" method="POST">
            <div>
                <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    E-mail Corporativo
                </label>
                <div class="relative">
                    <input id="email" 
                           name="email" 
                           type="email" 
                           required 
                           value="gerente@vianexo.com.br"
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm transition-all">
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                        <i class="fa-solid fa-envelope"></i>
                    </span>
                </div>
            </div>

            <div>
                <label for="senha" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Senha de Acesso (Bcrypt)
                </label>
                <div class="relative">
                    <input id="senha" 
                           name="senha" 
                           type="password" 
                           required 
                           value="admin123"
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm transition-all">
                    <button type="button" 
                            onclick="togglePasswordVisibility()" 
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600">
                        <i id="eye-icon" class="fa-solid fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between text-xs text-slate-500">
                <span class="flex items-center gap-1.5">
                    <i class="fa-solid fa-lock text-emerald-500"></i> Autenticação Criptografada
                </span>
                <span class="text-slate-400">Bcrypt Cost 10</span>
            </div>

            <div>
                <button type="submit" class="w-full flex justify-center items-center gap-2 py-3.5 px-4 border border-transparent rounded-xl text-sm font-bold text-white bg-brand-600 hover:bg-brand-700 active:bg-brand-800 shadow-lg shadow-brand-500/25 transition-all">
                    <i class="fa-solid fa-right-to-bracket"></i> Entrar no Painel Logístico
                </button>
            </div>
        </form>

        <div class="pt-4 border-t border-slate-100 text-center">
            <p class="text-xs text-slate-500">
                Credencial padrão de teste: <code class="font-mono bg-slate-100 px-1.5 py-0.5 rounded text-slate-800 font-bold">admin123</code>
            </p>
        </div>
    </div>
</div>

<script>
    function togglePasswordVisibility() {
        const input = document.getElementById('senha');
        const icon = document.getElementById('eye-icon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
