<?php
require_once '../includes/funcoes.php';

$tituloPagina = 'Novo cliente | Barbearia Prime';
$mensagem = $_GET['mensagem'] ?? '';
$tipo = $_GET['tipo'] ?? 'success';

require_once '../includes/cabecalho.php';
require_once '../includes/menu.php';
?>

<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h1 class="fw-bold mb-2">Cadastrar cliente</h1>
                    <p class="text-muted">Preencha os dados para criar um novo cliente.</p>

                    <?php if (campoObrigatorio($mensagem)): ?>
                        <div class="alert alert-<?= limparTexto($tipo); ?>">
                            <?= limparTexto($mensagem); ?>
                        </div>
                    <?php endif; ?>

                    <form action="salvar_cliente.php" method="post" class="row g-3">
                        <div class="col-md-8">
                            <label for="nome" class="form-label">Nome completo</label>
                            <input type="text" class="form-control" id="nome" name="nome" maxlength="100" required>
                        </div>

                        <div class="col-md-4">
                            <label for="data_nascimento" class="form-label">Data de nascimento</label>
                            <input type="text" class="form-control mascara-data" id="data_nascimento" name="data_nascimento" maxlength="10" placeholder="dd/mm/aaaa" required>
                        </div>

                        <div class="col-md-4">
                            <label for="cpf" class="form-label">CPF</label>
                            <input type="text" class="form-control" id="cpf" name="cpf" maxlength="14" placeholder="000.000.000-00" required>
                        </div>

                        <div class="col-md-4">
                            <label for="email" class="form-label">E-mail</label>
                            <input type="email" class="form-control" id="email" name="email" maxlength="100" required>
                        </div>

                        <div class="col-md-4">
                            <label for="telefone" class="form-label">Telefone</label>
                            <input type="text" class="form-control" id="telefone" name="telefone" maxlength="15" placeholder="(00) 00000-0000" required>
                        </div>

                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-dark">Salvar cliente</button>
                            <a href="index.php" class="btn btn-outline-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once '../includes/rodape.php'; ?>
