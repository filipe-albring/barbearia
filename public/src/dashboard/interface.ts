import { classeBadgeStatus, formatadorMoeda, numeroSeguro, textoSeguro } from './formatadores.js';
import type { ItemRanking, ProximoAgendamento, ResumoDashboard } from './tipos.js';

function elemento<K extends keyof HTMLElementTagNameMap>(tag: K, classe = '', texto = ''): HTMLElementTagNameMap[K] {
    const item = document.createElement(tag);
    item.className = classe;
    item.textContent = texto;
    return item;
}

function substituirConteudo(destino: Element | null, itens: Node[]): void {
    destino?.replaceChildren(...itens);
}

function atualizarTexto(seletor: string, valor: string): void {
    const destino = document.querySelector<HTMLElement>(seletor);

    if (destino) {
        destino.textContent = valor;
    }
}

function mensagemVazia(mensagem: string): HTMLElement {
    return elemento('p', 'dashboard-vazio', mensagem);
}

export function renderizarMetricas(resumo: ResumoDashboard, periodo: string): void {
    const metricas: Array<[string, string, string]> = [
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

function linhaRanking(item: ItemRanking, tipo: 'servico' | 'barbeiro', maiorValor: number): HTMLElement {
    const valorPrincipal = tipo === 'servico' ? item.quantidade : item.faturamento;
    const percentual = Math.max(4, (valorPrincipal / maiorValor) * 100);
    const descricao = tipo === 'servico'
        ? `${item.quantidade} serviço(s)`
        : `${formatadorMoeda.format(item.faturamento)} · ${item.quantidade} agenda(s)`;
    const cabecalho = elemento('div', 'd-flex justify-content-between gap-3 small mb-2');
    const barra = elemento('div', 'progress');
    const preenchimento = elemento('div', 'progress-bar');

    cabecalho.append(
        elemento('span', 'fw-semibold dashboard-ranking-nome', item.nome),
        elemento('span', 'text-muted text-end', descricao),
    );
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

export function renderizarRanking(
    destino: HTMLElement | null,
    itens: ItemRanking[],
    tipo: 'servico' | 'barbeiro',
): void {
    if (itens.length === 0) {
        substituirConteudo(destino, [mensagemVazia('Nenhum dado registrado para este período.')]);
        return;
    }

    const valores = itens.map((item) => tipo === 'servico' ? item.quantidade : item.faturamento);
    const maiorValor = Math.max(...valores, 1);
    substituirConteudo(destino, itens.slice(0, 5).map((item) => linhaRanking(item, tipo, maiorValor)));
}

function linhaAgendamento(agendamento: ProximoAgendamento): HTMLTableRowElement {
    const linha = elemento('tr');
    const dados = [agendamento.data_hora, agendamento.cliente, agendamento.barbeiro, agendamento.servicos];
    const badge = elemento('span', `badge ${classeBadgeStatus(agendamento.status)}`, textoSeguro(agendamento.status));
    const celulaStatus = elemento('td');
    const celulaValor = elemento('td', 'text-end fw-semibold', formatadorMoeda.format(numeroSeguro(agendamento.valor_total)));

    celulaStatus.append(badge);
    linha.append(
        ...dados.map((dado) => elemento('td', '', textoSeguro(dado))),
        celulaStatus,
        celulaValor,
    );
    return linha;
}

export function renderizarProximosAgendamentos(
    destino: HTMLTableSectionElement | null,
    agendamentos: ProximoAgendamento[],
): void {
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
