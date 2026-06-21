<?php
require_once '../config/conexao.php';
require_once '../includes/funcoes.php';

$conexao = conectarBanco();
$barbeiros = buscarBarbeiros($conexao);
$servicos = buscarServicos($conexao);
$tituloPagina = 'Agendar | Barbearia Prime';
$mensagem = $_GET['mensagem'] ?? '';
$tipo = $_GET['tipo'] ?? 'success';
$identificacao = $_GET['identificacao'] ?? '';

require_once '../includes/cabecalho.php';
require_once '../includes/menu.php';
?>

<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h1 class="fw-bold mb-2">Realizar agendamento</h1>
                    <p class="text-muted">Informe seu CPF, e-mail ou telefone para localizar seu cadastro e escolher o horário.</p>

                    <?php if (campoObrigatorio($mensagem)): ?>
                        <div class="alert alert-<?= limparTexto($tipo); ?>">
                            <?= limparTexto($mensagem); ?>
                        </div>
                    <?php endif; ?>

                    <form action="salvar_agendamento.php" method="post" class="row g-3">
                        <div class="col-12">
                            <label for="identificacao" class="form-label">CPF, e-mail ou telefone cadastrado</label>
                            <input
                                type="text"
                                class="form-control"
                                id="identificacao"
                                name="identificacao"
                                value="<?= limparTexto($identificacao); ?>"
                                placeholder="Ex.: 000.000.000-00, cliente@email.com ou (00) 00000-0000"
                                required
                            >
                            <div class="form-text">
                                Se ainda não possui cadastro, <a href="cliente_novo.php">cadastre-se aqui</a>.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="id_barbeiro" class="form-label">Barbeiro</label>
                            <select class="form-select" id="id_barbeiro" name="id_barbeiro" required>
                                <option value="">Selecione</option>
                                <?php foreach ($barbeiros as $barbeiro): ?>
                                    <option value="<?= (int) $barbeiro['id_barbeiro']; ?>">
                                        <?= limparTexto($barbeiro['nm_barbeiro']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="data_agendamento" class="form-label">Data</label>
                            <input
                                type="text"
                                class="form-control mascara-data"
                                id="data_agendamento"
                                name="data_agendamento"
                                placeholder="dd/mm/aaaa"
                                maxlength="10"
                                required
                            >
                        </div>

                        <div class="col-md-3">
                            <label for="hora_agendamento" class="form-label">Horário</label>
                            <input type="time" class="form-control" id="hora_agendamento" name="hora_agendamento" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Serviços</label>
                            <div class="row g-2">
                                <?php if (count($servicos) > 0): ?>
                                    <?php foreach ($servicos as $servico): ?>
                                        <div class="col-md-6">
                                            <div class="form-check border rounded p-3 ps-5 h-100">
                                                <input class="form-check-input" type="checkbox" name="servicos[]" value="<?= (int) $servico['id_servico']; ?>" id="servico_<?= (int) $servico['id_servico']; ?>">
                                                <label class="form-check-label" for="servico_<?= (int) $servico['id_servico']; ?>">
                                                    <?= limparTexto($servico['nm_servico']); ?>
                                                    <span class="text-muted">(<?= formatarMoeda((float) $servico['vl_preco']); ?>)</span>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="col-12">
                                        <div class="alert alert-warning mb-0">Nenhum serviço disponível para agendamento.</div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-12 d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-dark">Confirmar agendamento</button>
                            <a href="agendamentos.php" class="btn btn-outline-secondary">Consultar meus agendamentos</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once '../includes/rodape.php'; ?>
