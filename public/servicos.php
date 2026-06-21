<?php
require_once '../config/conexao.php';
require_once '../includes/funcoes.php';

$conexao = conectarBanco();
$servicos = buscarServicos($conexao);
$tituloPagina = 'Serviços | Barbearia Prime';

require_once '../includes/cabecalho.php';
require_once '../includes/menu.php';
?>

<main class="container py-5">
    <h1 class="fw-bold mb-2">Serviços</h1>
    <p class="text-muted mb-4">Serviços previamente cadastrados no banco de dados.</p>

    <div class="row g-4">
        <?php if (count($servicos) > 0): ?>
            <?php foreach ($servicos as $servico): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <h2 class="h5"><?= limparTexto($servico['nm_servico']); ?></h2>
                            <p class="text-muted">Atendimento com horário reservado.</p>
                            <span class="btn btn-dark disabled"><?= formatarMoeda((float) $servico['vl_preco']); ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-warning">Ainda não existem serviços cadastrados.</div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once '../includes/rodape.php'; ?>
