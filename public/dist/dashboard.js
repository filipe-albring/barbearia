import { formatarAtualizacao } from './dashboard/formatadores.js';
import { calcularResumo } from './dashboard/resumo.js';
import { renderizarMetricas, renderizarProximosAgendamentos, renderizarRanking, } from './dashboard/interface.js';
const pagina = document.querySelector('.dashboard-page');
const conteudo = document.querySelector('#dashboard-conteudo');
const seletorPeriodo = document.querySelector('#periodo-dashboard');
const botaoAtualizar = document.querySelector('#atualizar-dashboard');
const alertaErro = document.querySelector('#dashboard-erro');
const atualizadoEm = document.querySelector('#dashboard-atualizado-em');
const rankingServicos = document.querySelector('#ranking-servicos');
const rankingBarbeiros = document.querySelector('#ranking-barbeiros');
const tabelaProximos = document.querySelector('#proximos-agendamentos');
const totalServicosBadge = document.querySelector('#total-servicos-badge');
let requisicaoAtual = null;
function rotuloPeriodo() {
    if (!seletorPeriodo || seletorPeriodo.value === '0') {
        return 'em todo o histórico';
    }
    return `nos últimos ${seletorPeriodo.value} dias`;
}
function exibirErro(mensagem = '') {
    if (!alertaErro) {
        return;
    }
    alertaErro.textContent = mensagem;
    alertaErro.classList.toggle('d-none', mensagem === '');
}
function definirCarregamento(estaCarregando) {
    if (botaoAtualizar) {
        botaoAtualizar.disabled = estaCarregando;
        botaoAtualizar.textContent = estaCarregando ? 'Atualizando...' : 'Atualizar';
    }
    conteudo?.setAttribute('aria-busy', String(estaCarregando));
}
async function carregarDashboard() {
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
        const dados = await resposta.json();
        if (!resposta.ok || !dados.ok) {
            throw new Error(dados.mensagem ?? 'O servidor não conseguiu retornar os indicadores.');
        }
        const resumo = calcularResumo(Array.isArray(dados.itens_financeiros) ? dados.itens_financeiros : []);
        renderizarMetricas(resumo, rotuloPeriodo());
        renderizarRanking(rankingServicos, resumo.servicos, 'servico');
        renderizarRanking(rankingBarbeiros, resumo.barbeiros, 'barbeiro');
        renderizarProximosAgendamentos(tabelaProximos, Array.isArray(dados.proximos_agendamentos) ? dados.proximos_agendamentos : []);
        if (totalServicosBadge) {
            const total = resumo.servicos.reduce((acumulado, servico) => acumulado + servico.quantidade, 0);
            totalServicosBadge.textContent = `${total} serviço(s)`;
        }
        if (atualizadoEm) {
            atualizadoEm.textContent = formatarAtualizacao(dados.gerado_em);
        }
    }
    catch (erro) {
        if (controlador.signal.aborted && requisicaoAtual !== controlador) {
            return;
        }
        const timeout = erro instanceof DOMException && erro.name === 'AbortError';
        exibirErro(timeout
            ? 'A consulta demorou mais que o esperado. Tente atualizar novamente.'
            : 'Não foi possível carregar o dashboard. Verifique a conexão com o banco e se a migração analítica foi executada.');
        console.error('Erro ao carregar o dashboard:', erro);
    }
    finally {
        window.clearTimeout(temporizador);
        if (requisicaoAtual === controlador) {
            definirCarregamento(false);
        }
    }
}
botaoAtualizar?.addEventListener('click', () => void carregarDashboard());
seletorPeriodo?.addEventListener('change', () => void carregarDashboard());
void carregarDashboard();
