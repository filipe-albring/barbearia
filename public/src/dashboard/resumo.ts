import { dataHoje, numeroSeguro, statusCancelado, textoSeguro } from './formatadores.js';
import type { ItemFinanceiro, ItemRanking, ResumoDashboard } from './tipos.js';

interface Acumulador {
    faturamento: number;
    agendamentosAtivos: Set<string>;
    agendamentosHoje: Set<string>;
    cancelamentos: Set<string>;
    servicos: Map<string, ItemRanking>;
    barbeiros: Map<string, ItemRanking & { agendamentos: Set<string> }>;
}

function criarAcumulador(): Acumulador {
    return {
        faturamento: 0,
        agendamentosAtivos: new Set(),
        agendamentosHoje: new Set(),
        cancelamentos: new Set(),
        servicos: new Map(),
        barbeiros: new Map(),
    };
}

function ordenarRanking(itens: ItemRanking[], campo: 'quantidade' | 'faturamento'): ItemRanking[] {
    return itens.sort((primeiro, segundo) => {
        const diferenca = segundo[campo] - primeiro[campo];
        return diferenca || segundo.faturamento - primeiro.faturamento;
    });
}

export function calcularResumo(itens: ItemFinanceiro[]): ResumoDashboard {
    const hoje = dataHoje();

    // Consolida as métricas diretamente do array bruto retornado pela API PHP.
    const acumulado = itens.reduce<Acumulador>((resultado, item) => {
        const id = String(item.id_agendamento);

        if (statusCancelado(item.status)) {
            resultado.cancelamentos.add(id);
            return resultado;
        }

        const valor = numeroSeguro(item.valor_unitario);
        const servico = textoSeguro(item.servico, 'Serviço não informado');
        const barbeiro = textoSeguro(item.barbeiro, 'Barbeiro não informado');
        const dadosServico = resultado.servicos.get(servico) ?? { nome: servico, quantidade: 0, faturamento: 0 };
        const dadosBarbeiro = resultado.barbeiros.get(barbeiro) ?? {
            nome: barbeiro, quantidade: 0, faturamento: 0, agendamentos: new Set<string>(),
        };

        resultado.faturamento += valor;
        resultado.agendamentosAtivos.add(id);
        dadosServico.quantidade += 1;
        dadosServico.faturamento += valor;
        dadosBarbeiro.faturamento += valor;
        dadosBarbeiro.agendamentos.add(id);
        dadosBarbeiro.quantidade = dadosBarbeiro.agendamentos.size;

        if (item.data_agendamento === hoje) {
            resultado.agendamentosHoje.add(id);
        }

        resultado.servicos.set(servico, dadosServico);
        resultado.barbeiros.set(barbeiro, dadosBarbeiro);
        return resultado;
    }, criarAcumulador());

    const agendamentosAtivos = acumulado.agendamentosAtivos.size;

    return {
        faturamento: acumulado.faturamento,
        agendamentosAtivos,
        agendamentosHoje: acumulado.agendamentosHoje.size,
        cancelamentos: acumulado.cancelamentos.size,
        ticketMedio: agendamentosAtivos ? acumulado.faturamento / agendamentosAtivos : 0,
        servicos: ordenarRanking([...acumulado.servicos.values()], 'quantidade'),
        barbeiros: ordenarRanking([...acumulado.barbeiros.values()], 'faturamento'),
    };
}
