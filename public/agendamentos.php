<?php
require_once '../config/conexao.php';
require_once '../includes/funcoes.php';

$conexao = conectarBanco();
$tituloPagina = 'Meus agendamentos | Barbearia Prime';
$mensagem = $_GET['mensagem'] ?? '';
$tipo = $_GET['tipo'] ?? 'success';
$identificacao = $_GET['identificacao'] ?? '';
$cliente = null;
$agendamentos = [];

if (campoObrigatorio($identificacao)) {
    $cliente = buscarClientePorIdentificacao($conexao, $identificacao);

    if ($cliente) {
        $agendamentos = listarProximosAgendamentosCliente($conexao, (int) $cliente['id_cliente']);
    } else {
        $tipo = 'warning';
        $mensagem = 'Cliente não encontrado. Verifique os dados informados ou faça seu cadastro.';
    }
}

require_once '../includes/cabecalho.php';
require_once '../includes/menu.php';
?>

<main class="container py-5">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="fw-bold mb-2">Meus agendamentos</h1>
            <p class="text-muted mb-0">Consulte seus próximos horários usando CPF, e-mail ou telefone.</p>
        </div>
        <a href="agendar.php" class="btn btn-dark">Novo agendamento</a>
    </div>

    <?php if (campoObrigatorio($mensagem)): ?>
        <div class="alert alert-<?= limparTexto($tipo); ?>">
            <?= limparTexto($mensagem); ?>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="agendamentos.php" method="get" class="row g-3 align-items-end">
                <div class="col-md-9">
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
                </div>
                <div class="col-md-3 d-grid">
                    <button type="submit" class="btn btn-dark">Consultar</button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($cliente && count($agendamentos) > 0): ?>
        <div class="alert alert-light border">
            Exibindo agendamentos de <strong><?= limparTexto($cliente['nm_cliente']); ?></strong>.
        </div>

        <div class="table-responsive shadow-sm rounded">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Data e horário</th>
                        <th>Barbeiro</th>
                        <th>Serviços</th>
                        <th>Status</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($agendamentos as $agendamento): ?>
                        <tr>
                            <td><?= formatarDataHora($agendamento['dt_hora']); ?></td>
                            <td><?= limparTexto($agendamento['nm_barbeiro']); ?></td>
                            <td><?= limparTexto($agendamento['servicos']); ?></td>
                            <td><span class="badge text-bg-success"><?= limparTexto($agendamento['status']); ?></span></td>
                            <td>
                                <form action="cancelar_agendamento.php" method="post" onsubmit="return confirm('Deseja cancelar este agendamento?');">
                                    <input type="hidden" name="id_agendamento" value="<?= (int) $agendamento['id_agendamento']; ?>">
                                    <input type="hidden" name="identificacao" value="<?= limparTexto($identificacao); ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm">Cancelar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php elseif ($cliente): ?>
        <div class="alert alert-info">
            Você não possui próximos agendamentos cadastrados.
        </div>
    <?php endif; ?>
</main>

<?php require_once '../includes/rodape.php'; ?>
