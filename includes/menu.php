<?php $paginaAtual = basename($_SERVER['PHP_SELF'] ?? 'index.php'); ?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">Barbearia Prime</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal" aria-controls="menuPrincipal" aria-expanded="false" aria-label="Abrir menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="menuPrincipal">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link<?= $paginaAtual === 'dashboard.php' ? ' active' : ''; ?>" href="dashboard.php"<?= $paginaAtual === 'dashboard.php' ? ' aria-current="page"' : ''; ?>>Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?= $paginaAtual === 'index.php' ? ' active' : ''; ?>" href="index.php"<?= $paginaAtual === 'index.php' ? ' aria-current="page"' : ''; ?>>Início</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?= $paginaAtual === 'servicos.php' ? ' active' : ''; ?>" href="servicos.php"<?= $paginaAtual === 'servicos.php' ? ' aria-current="page"' : ''; ?>>Serviços</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?= $paginaAtual === 'barbeiros.php' ? ' active' : ''; ?>" href="barbeiros.php"<?= $paginaAtual === 'barbeiros.php' ? ' aria-current="page"' : ''; ?>>Barbeiros</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?= $paginaAtual === 'cliente_novo.php' ? ' active' : ''; ?>" href="cliente_novo.php"<?= $paginaAtual === 'cliente_novo.php' ? ' aria-current="page"' : ''; ?>>Novo cliente</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?= $paginaAtual === 'agendar.php' ? ' active' : ''; ?>" href="agendar.php"<?= $paginaAtual === 'agendar.php' ? ' aria-current="page"' : ''; ?>>Agendar</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?= $paginaAtual === 'agendamentos.php' ? ' active' : ''; ?>" href="agendamentos.php"<?= $paginaAtual === 'agendamentos.php' ? ' aria-current="page"' : ''; ?>>Agendamentos</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
