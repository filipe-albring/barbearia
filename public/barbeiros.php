<?php
require_once '../config/conexao.php';
require_once '../includes/funcoes.php';

$conexao = conectarBanco();
$barbeiros = buscarBarbeiros($conexao);
$tituloPagina = 'Barbeiros | Barbearia Prime';

require_once '../includes/cabecalho.php';
require_once '../includes/menu.php';
?>

<main class="container py-5">
    <h1 class="fw-bold mb-2">Barbeiros</h1>
    <p class="text-muted mb-4">Profissionais previamente cadastrados no banco de dados.</p>

    <div class="row g-4">
        <?php if (count($barbeiros) > 0): ?>
            <?php foreach ($barbeiros as $barbeiro): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <h2 class="h5"><?= limparTexto($barbeiro['nm_barbeiro']); ?></h2>
                            <p class="text-muted">Profissional disponível para agendamentos.</p>

                            <?php if (campoObrigatorio($barbeiro['emails'] ?? '')): ?>
                                <p class="mb-1">
                                    <strong>E-mail:</strong> <?= limparTexto($barbeiro['emails']); ?>
                                </p>
                            <?php endif; ?>

                            <?php if (campoObrigatorio($barbeiro['telefones'] ?? '')): ?>
                                <p class="mb-0">
                                    <strong>Telefone:</strong> <?= limparTexto($barbeiro['telefones']); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-warning">Ainda não existem barbeiros cadastrados.</div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once '../includes/rodape.php'; ?>
