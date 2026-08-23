<?php
require_once '../includes/funcoes.php';

$tituloPagina = 'Dashboard | Barbearia Prime';

require_once '../includes/cabecalho.php';
require_once '../includes/menu.php';
?>

<main class="container py-4 py-lg-5 dashboard-page" data-api-url="api/dashboard.php">
    <section class="dashboard-cabecalho p-4 p-lg-5 mb-4 shadow-sm">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <span class="badge text-bg-warning mb-3">Painel de gestão</span>
                <h1 class="display-6 fw-bold mb-2 text-white">Visão geral da barbearia</h1>
                <p class="mb-0 text-white-50">Dashboard administrativo da Barbearia Prime</p>
            </div>
            <div class="col-lg-5">
                <div class="dashboard-filtros bg-white rounded-4 p-3">
                    <label for="periodo-dashboard" class="form-label fw-semibold text-dark">Período analisado</label>
                    <div class="input-group">
                        <select id="periodo-dashboard" class="form-select" aria-label="Selecionar período do dashboard">
                            <option value="7">Últimos 7 dias</option>
                            <option value="30" selected>Últimos 30 dias</option>
                            <option value="90">Últimos 90 dias</option>
                            <option value="0">Todo o histórico</option>
                        </select>
                        <button id="atualizar-dashboard" class="btn btn-dark" type="button">Atualizar</button>
                    </div>
                    <small id="dashboard-atualizado-em" class="text-muted d-block mt-2">Carregando informações...</small>
                </div>
            </div>
        </div>
    </section>

    <div id="dashboard-erro" class="alert alert-danger d-none" role="alert"></div>

    <section aria-label="Indicadores principais" aria-live="polite" aria-busy="true" id="dashboard-conteudo">
        <div class="row g-3 g-lg-4 mb-4">
            <div class="col-sm-6 col-xl-3">
                <article class="card dashboard-card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted text-uppercase small fw-semibold mb-2">Faturamento previsto</p>
                                <p class="dashboard-valor mb-1" data-metrica="faturamento">—</p>
                                <small class="text-muted" data-legenda="faturamento">Aguardando dados</small>
                            </div>
                            <span class="dashboard-icone bg-warning-subtle text-warning-emphasis" aria-hidden="true">R$</span>
                        </div>
                    </div>
                </article>
            </div>
            <div class="col-sm-6 col-xl-3">
                <article class="card dashboard-card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted text-uppercase small fw-semibold mb-2">Agendamentos ativos</p>
                                <p class="dashboard-valor mb-1" data-metrica="agendamentos">—</p>
                                <small class="text-muted" data-legenda="agendamentos">Aguardando dados</small>
                            </div>
                            <span class="dashboard-icone bg-dark text-white" aria-hidden="true">#</span>
                        </div>
                    </div>
                </article>
            </div>
            <div class="col-sm-6 col-xl-3">
                <article class="card dashboard-card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted text-uppercase small fw-semibold mb-2">Ticket médio previsto</p>
                                <p class="dashboard-valor mb-1" data-metrica="ticket-medio">—</p>
                                <small class="text-muted" data-legenda="ticket-medio">Aguardando dados</small>
                            </div>
                            <span class="dashboard-icone bg-success-subtle text-success-emphasis" aria-hidden="true">+</span>
                        </div>
                    </div>
                </article>
            </div>
            <div class="col-sm-6 col-xl-3">
                <article class="card dashboard-card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted text-uppercase small fw-semibold mb-2">Cancelamentos</p>
                                <p class="dashboard-valor mb-1" data-metrica="cancelamentos">—</p>
                                <small class="text-muted" data-legenda="cancelamentos">Aguardando dados</small>
                            </div>
                            <span class="dashboard-icone bg-danger-subtle text-danger-emphasis" aria-hidden="true">×</span>
                        </div>
                    </div>
                </article>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-7">
                <section class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
                            <div>
                                <h2 class="h4 fw-bold mb-1">Serviços mais procurados</h2>
                                <p class="text-muted mb-0">Quantidade de serviços em agendamentos ativos.</p>
                            </div>
                            <span class="badge text-bg-dark" id="total-servicos-badge">0 serviços</span>
                        </div>
                        <div id="ranking-servicos" class="vstack gap-3"></div>
                    </div>
                </section>
            </div>
            <div class="col-lg-5">
                <section class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="h4 fw-bold mb-1">Desempenho por barbeiro</h2>
                        <p class="text-muted mb-4">Faturamento previsto por profissional.</p>
                        <div id="ranking-barbeiros" class="vstack gap-3"></div>
                    </div>
                </section>
            </div>
        </div>

        <section class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="p-4 border-bottom d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                    <div>
                        <h2 class="h4 fw-bold mb-1">Próximos agendamentos</h2>
                        <p class="text-muted mb-0">Os cinco horários futuros mais próximos.</p>
                    </div>
                    <a href="agendar.php" class="btn btn-dark">Novo agendamento</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Data e horário</th>
                                <th scope="col">Cliente</th>
                                <th scope="col">Barbeiro</th>
                                <th scope="col">Serviços</th>
                                <th scope="col">Status</th>
                                <th scope="col" class="text-end">Valor previsto</th>
                            </tr>
                        </thead>
                        <tbody id="proximos-agendamentos">
                            <tr><td colspan="6" class="text-center text-muted py-4">Carregando agendamentos...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </section>
</main>

<script type="module" src="dist/dashboard.js"></script>
<?php require_once '../includes/rodape.php'; ?>
