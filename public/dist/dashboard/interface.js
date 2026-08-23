import { classeBadgeStatus, formatadorMoeda, numeroSeguro, textoSeguro } from './formatadores.js';
function elemento(tag, classe = '', texto = '') {
    const item = document.createElement(tag);
    item.className = classe;
    item.textContent = texto;
    return item;
}
function substituirConteudo(destino, itens) {
    destino?.replaceChildren(...itens);
}
function atualizarTexto(seletor, valor) {
    const destino = document.querySelector(seletor);
    if (destino) {
        destino.textContent = valor;
    }
}
function mensagemVazia(mensagem) {
    return elemento('p', 'dashboard-vazio', mensagem);
}
export function renderizarMetricas(resumo, periodo) {
    const metricas = [
        ['faturamento', formatadorMoeda.format(resumo.faturamento), `Valor estimado ${periodo}`],
        ['agendamentos', String(resumo.agendamentosAtivos), `${resumo.agendamentosHoje} agendamento(s) para hoje`],
        ['ticket-medio', formatadorMoeda.format(resumo.ticketMedio), 'Por agendamento ativo'],
        ['cancelamentos', String(resumo.cancelamentos), `Registros ${periodo}`],
    ];
    metricas.forEach(([nome, valor, legenda]) => {
        atualizarTexto(`[data-metrica="${nome}"]`, valor);
        atualizarTexto(`[data-legenda="${nome}"]`, legenda);
    });
}
function linhaRanking(item, tipo, maiorValor) {
    const valorPrincipal = tipo === 'servico' ? item.quantidade : item.faturamento;
    const percentual = Math.max(4, (valorPrincipal / maiorValor) * 100);
    const descricao = tipo === 'servico'
        ? `${item.quantidade} serviço(s)`
        : `${formatadorMoeda.format(item.faturamento)} · ${item.quantidade} agenda(s)`;
    const cabecalho = elemento('div', 'd-flex justify-content-between gap-3 small mb-2');
    const barra = elemento('div', 'progress');
    const preenchimento = elemento('div', 'progress-bar');
    cabecalho.append(elemento('span', 'fw-semibold dashboard-ranking-nome', item.nome), elemento('span', 'text-muted text-end', descricao));
    preenchimento.setAttribute('role', 'progressbar');
    preenchimento.setAttribute('aria-label', `${item.nome}: ${descricao}`);
    preenchimento.setAttribute('aria-valuenow', String(Math.round(percentual)));
    preenchimento.setAttribute('aria-valuemin', '0');
    preenchimento.setAttribute('aria-valuemax', '100');
    preenchimento.style.width = `${percentual}%`;
    barra.append(preenchimento);
    const linha = elemento('div');
    linha.append(cabecalho, barra);
    return linha;
}
export function renderizarRanking(destino, itens, tipo) {
    if (itens.length === 0) {
        substituirConteudo(destino, [mensagemVazia('Nenhum dado registrado para este período.')]);
        return;
    }
    const valores = itens.map((item) => tipo === 'servico' ? item.quantidade : item.faturamento);
    const maiorValor = Math.max(...valores, 1);
    substituirConteudo(destino, itens.slice(0, 5).map((item) => linhaRanking(item, tipo, maiorValor)));
}
function linhaAgendamento(agendamento) {
    const linha = elemento('tr');
    const dados = [agendamento.data_hora, agendamento.cliente, agendamento.barbeiro, agendamento.servicos];
    const badge = elemento('span', `badge ${classeBadgeStatus(agendamento.status)}`, textoSeguro(agendamento.status));
    const celulaStatus = elemento('td');
    const celulaValor = elemento('td', 'text-end fw-semibold', formatadorMoeda.format(numeroSeguro(agendamento.valor_total)));
    celulaStatus.append(badge);
    linha.append(...dados.map((dado) => elemento('td', '', textoSeguro(dado))), celulaStatus, celulaValor);
    return linha;
}
export function renderizarProximosAgendamentos(destino, agendamentos) {
    if (agendamentos.length > 0) {
        substituirConteudo(destino, agendamentos.map(linhaAgendamento));
        return;
    }
    const linha = elemento('tr');
    const celula = elemento('td', 'text-center text-muted py-4', 'Nenhum agendamento futuro registrado.');
    celula.colSpan = 6;
    linha.append(celula);
    substituirConteudo(destino, [linha]);
}
