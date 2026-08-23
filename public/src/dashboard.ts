import { formatarAtualizacao } from './dashboard/formatadores.js';
import { calcularResumo } from './dashboard/resumo.js';
import {
    renderizarMetricas,
    renderizarProximosAgendamentos,
    renderizarRanking,
} from './dashboard/interface.js';
import type { RespostaDashboard } from './dashboard/tipos.js';

const pagina = document.querySelector<HTMLElement>('.dashboard-page');
const conteudo = document.querySelector<HTMLElement>('#dashboard-conteudo');
const seletorPeriodo = document.querySelector<HTMLSelectElement>('#periodo-dashboard');
const botaoAtualizar = document.querySelector<HTMLButtonElement>('#atualizar-dashboard');
const alertaErro = document.querySelector<HTMLElement>('#dashboard-erro');
const atualizadoEm = document.querySelector<HTMLElement>('#dashboard-atualizado-em');
const rankingServicos = document.querySelector<HTMLElement>('#ranking-servicos');
const rankingBarbeiros = document.querySelector<HTMLElement>('#ranking-barbeiros');
const tabelaProximos = document.querySelector<HTMLTableSectionElement>('#proximos-agendamentos');
const totalServicosBadge = document.querySelector<HTMLElement>('#total-servicos-badge');

let requisicaoAtual: AbortController | null = null;

function rotuloPeriodo(): string {
    if (!seletorPeriodo || seletorPeriodo.value === '0') {
        return 'em todo o histórico';
    }

    return `nos últimos ${seletorPeriodo.value} dias`;
}

function exibirErro(mensagem = ''): void {
    if (!alertaErro) {
        return;
    }

    alertaErro.textContent = mensagem;
    alertaErro.classList.toggle('d-none', mensagem === '');
}

function definirCarregamento(estaCarregando: boolean): void {
    if (botaoAtualizar) {
        botaoAtualizar.disabled = estaCarregando;
        botaoAtualizar.textContent = estaCarregando ? 'Atualizando...' : 'Atualizar';
    }

    conteudo?.setAttribute('aria-busy', String(estaCarregando));
}

async function carregarDashboard(): Promise<void> {
    if (!pagina || !seletorPeriodo) {
        return;
    }

    requisicaoAtual?.abort();
    const controlador = new AbortController();
    requisicaoAtual = controlador;
    const temporizador = window.setTimeout(() => controlador.abort(), 10000);
    const url = new URL(pagina.dataset.apiUrl ?? 'api/dashboard.php', window.location.href);
    url.searchParams.set('periodo', seletorPeriodo.value);

    definirCarregamento(true);
    exibirErro();

    try {
        const resposta = await fetch(url, {
            headers: { Accept: 'application/json' },
            signal: controlador.signal,
        });
        const dados = await resposta.json() as RespostaDashboard;

        if (!resposta.ok || !dados.ok) {
            throw new Error(dados.mensagem ?? 'O servidor não conseguiu retornar os indicadores.');
        }

        const resumo = calcularResumo(Array.isArray(dados.itens_financeiros) ? dados.itens_financeiros : []);
        renderizarMetricas(resumo, rotuloPeriodo());
        renderizarRanking(rankingServicos, resumo.servicos, 'servico');
        renderizarRanking(rankingBarbeiros, resumo.barbeiros, 'barbeiro');
        renderizarProximosAgendamentos(
            tabelaProximos,
            Array.isArray(dados.proximos_agendamentos) ? dados.proximos_agendamentos : [],
        );

        if (totalServicosBadge) {
            const total = resumo.servicos.reduce((acumulado, servico) => acumulado + servico.quantidade, 0);
            totalServicosBadge.textContent = `${total} serviço(s)`;
        }

        if (atualizadoEm) {
            atualizadoEm.textContent = formatarAtualizacao(dados.gerado_em);
        }
    } catch (erro) {
        if (controlador.signal.aborted && requisicaoAtual !== controlador) {
            return;
        }

        const timeout = erro instanceof DOMException && erro.name === 'AbortError';
        exibirErro(timeout
            ? 'A consulta demorou mais que o esperado. Tente atualizar novamente.'
            : 'Não foi possível carregar o dashboard. Verifique a conexão com o banco e se a migração analítica foi executada.');
        console.error('Erro ao carregar o dashboard:', erro);
    } finally {
        window.clearTimeout(temporizador);

        if (requisicaoAtual === controlador) {
            definirCarregamento(false);
        }
    }
}

botaoAtualizar?.addEventListener('click', () => void carregarDashboard());
seletorPeriodo?.addEventListener('change', () => void carregarDashboard());
void carregarDashboard();
