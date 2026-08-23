export type ValorDoBanco = number | string | null;

export interface ItemFinanceiro {
    id_agendamento: number | string;
    data_agendamento: string;
    status: string;
    barbeiro: string;
    servico: string;
    valor_unitario: ValorDoBanco;
}

export interface ProximoAgendamento {
    data_hora: string;
    status: string;
    cliente: string;
    barbeiro: string;
    servicos: string;
    valor_total: ValorDoBanco;
}

export interface RespostaDashboard {
    ok: boolean;
    mensagem?: string;
    gerado_em?: string;
    itens_financeiros?: ItemFinanceiro[];
    proximos_agendamentos?: ProximoAgendamento[];
}

export interface ItemRanking {
    nome: string;
    quantidade: number;
    faturamento: number;
}

export interface ResumoDashboard {
    faturamento: number;
    agendamentosAtivos: number;
    agendamentosHoje: number;
    cancelamentos: number;
    ticketMedio: number;
    servicos: ItemRanking[];
    barbeiros: ItemRanking[];
}
