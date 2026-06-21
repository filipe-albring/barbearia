<?php
require_once '../config/conexao.php';
require_once '../includes/funcoes.php';

$conexao = conectarBanco();
$servicos = buscarServicos($conexao);
$barbeiros = buscarBarbeiros($conexao);
$tituloPagina = 'Início | Barbearia Prime';

require_once '../includes/cabecalho.php';
require_once '../includes/menu.php';
?>

<main>
    <section class="hero text-white">
        <div class="container-fluid px-4 px-lg-5 py-5">
            <div class="row align-items-center g-4">
                <div class="col-lg-6">
                    <span class="badge text-bg-warning mb-3">Agendamento online</span>
                    <h1 class="display-5 fw-bold">Barbearia Prime</h1>
                    <p class="lead">Cortes, barba e cuidado masculino com horário marcado e atendimento organizado.</p>
                    <a href="agendar.php" class="btn btn-warning btn-lg fw-semibold">Realizar agendamento</a>
                </div>
                <div class="col-lg-6">
                    <div class="logo-box text-center">
                        <img src="imgs/logo-comp.png" alt="Logo da Barbearia Prime" class="img-fluid logo-principal mx-auto">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">Serviços</h2>
                <p class="text-muted mb-0">Confira os serviços disponíveis.</p>
            </div>
            <a href="servicos.php" class="btn btn-outline-dark">Ver todos</a>
        </div>

        <div class="row g-4">
            <?php if (count($servicos) > 0): ?>
                <?php foreach (array_slice($servicos, 0, 3) as $servico): ?>
                    <div class="col-md-4">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-body">
                                <h3 class="h5 card-title"><?= limparTexto($servico['nm_servico']); ?></h3>
                                <p class="card-text text-muted">Serviço profissional para renovar o visual.</p>
                                <span class="badge text-bg-dark"><?= formatarMoeda((float) $servico['vl_preco']); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info">Nenhum serviço cadastrado até o momento.</div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="container pb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">Barbeiros</h2>
                <p class="text-muted mb-0">Escolha seu profissional favorito.</p>
            </div>
            <a href="barbeiros.php" class="btn btn-outline-dark">Ver todos</a>
        </div>

        <div class="row g-4">
            <?php if (count($barbeiros) > 0): ?>
                <?php foreach (array_slice($barbeiros, 0, 3) as $barbeiro): ?>
                    <div class="col-md-4">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-body">
                                <h3 class="h5 card-title"><?= limparTexto($barbeiro['nm_barbeiro']); ?></h3>
                                <p class="card-text text-muted mb-2">Especialista em cortes e barba.</p>

                                <?php if (campoObrigatorio($barbeiro['telefones'] ?? '')): ?>
                                    <small class="text-muted">Contato: <?= limparTexto($barbeiro['telefones']); ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info">Nenhum barbeiro cadastrado até o momento.</div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require_once '../includes/rodape.php'; ?>
