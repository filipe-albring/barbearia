import type { ValorDoBanco } from './tipos.js';

export const formatadorMoeda = new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
});

export function textoSeguro(valor: unknown, alternativa = 'Não informado'): string {
    return String(valor ?? '').trim() || alternativa;
}

export function numeroSeguro(valor: ValorDoBanco): number {
    const numero = typeof valor === 'number'
        ? valor
        : Number.parseFloat(String(valor ?? '0').replace(',', '.'));

    return Number.isFinite(numero) ? Math.max(0, numero) : 0;
}

export function statusCancelado(status: string): boolean {
    return textoSeguro(status, '').toLocaleLowerCase('pt-BR').startsWith('cancel');
}

export function dataHoje(): string {
    const hoje = new Date();
    return [hoje.getFullYear(), hoje.getMonth() + 1, hoje.getDate()]
        .map((valor, indice) => indice === 0 ? String(valor) : String(valor).padStart(2, '0'))
        .join('-');
}

export function classeBadgeStatus(status: string): string {
    const valor = textoSeguro(status, '').toLocaleLowerCase('pt-BR');

    if (valor.startsWith('cancel')) {
        return 'text-bg-danger';
    }

    return valor.includes('conclu') || valor.includes('finaliz') ? 'text-bg-success' : 'text-bg-warning';
}

export function formatarAtualizacao(data: string | undefined): string {
    const dataConvertida = data ? new Date(data) : new Date();

    if (Number.isNaN(dataConvertida.getTime())) {
        return 'Dados atualizados agora';
    }

    return `Atualizado em ${new Intl.DateTimeFormat('pt-BR', {
        dateStyle: 'short',
        timeStyle: 'short',
    }).format(dataConvertida)}`;
}
