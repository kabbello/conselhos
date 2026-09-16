<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Lista de Presença — {{ $reuniao->conselho->nome ?? '' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            color: #000;
            background: #fff;
        }

        .page {
            width: 100%;
            padding: 12mm 14mm 14mm 14mm;
        }

        /* ── Cabeçalho ── */
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        .header .municipio-nome {
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header .conselho-nome {
            font-size: 11pt;
            font-weight: bold;
            margin-top: 3px;
        }
        .header .doc-titulo {
            font-size: 9pt;
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #444;
        }

        /* ── Info da reunião ── */
        .info-box {
            border: 1px solid #000;
            padding: 5px 10px;
            margin-bottom: 10px;
        }
        .info-box table { width: 100%; border-collapse: collapse; }
        .info-box td {
            padding: 2px 5px;
            font-size: 9pt;
            vertical-align: top;
        }
        .info-box td.label {
            font-weight: bold;
            white-space: nowrap;
            width: 130px;
        }

        /* ── Seções ── */
        .section-title {
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 1px solid #000;
            padding-bottom: 2px;
            margin-bottom: 5px;
            letter-spacing: 0.4px;
        }

        .pauta-section { margin-bottom: 10px; }
        .pauta-content {
            font-size: 9pt;
            line-height: 1.4;
            padding: 3px 0;
            white-space: pre-wrap;
        }

        /* ── Tabela de presença ── */
        .presenca-section { margin-bottom: 12px; }

        .presenca-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }
        .presenca-table th {
            background-color: #e0e0e0;
            border: 1px solid #000;
            padding: 4px 5px;
            text-align: left;
            font-size: 8.5pt;
            font-weight: bold;
        }
        .presenca-table td {
            border: 1px solid #000;
            padding: 3px 5px;
            vertical-align: middle;
            font-size: 8.5pt;
        }
        .presenca-table tr:nth-child(even) td { background-color: #f7f7f7; }

        .col-nome       { width: 30%; }
        .col-cargo      { width: 16%; }
        .col-entidade   { width: 22%; }
        .col-assinatura { width: 32%; }

        .assinatura-cell { height: 20px; }

        .total-label {
            font-size: 7.5pt;
            color: #555;
            margin-top: 3px;
        }

        /* ── Visitantes ── */
        .visitors-section {
            margin-top: 10px;
            border-top: 1px dashed #777;
            padding-top: 7px;
            page-break-inside: avoid;
        }
        .visitors-section .section-title { border-bottom: none; margin-bottom: 5px; }
        .visitors-line {
            border-bottom: 1px solid #bbb;
            height: 18px;
            margin-bottom: 3px;
        }

        /* ── Rodapé ── */
        .page-footer {
            margin-top: 14px;
            border-top: 1px solid #ccc;
            padding-top: 4px;
            font-size: 7.5pt;
            color: #666;
            text-align: center;
        }

        .text-center { text-align: center; }
    </style>
</head>
<body>
<div class="page">

    {{-- Cabeçalho --}}
    <div class="header">
        <div class="municipio-nome">Prefeitura Municipal de {{ $reuniao->conselho->municipio->nome ?? '' }}</div>
        <div class="conselho-nome">{{ $reuniao->conselho->nome ?? '' }}</div>
        <div class="doc-titulo">Lista de Presença</div>
    </div>

    {{-- Informações da Reunião --}}
    <div class="info-box">
        <table>
            <tr>
                <td class="label">Nº da Reunião:</td>
                <td>{{ $reuniao->numero ? '#'.$reuniao->numero : '—' }}</td>
                <td class="label">Tipo:</td>
                <td>{{ $reuniao->tipoReuniao->nome ?? '—' }}</td>
            </tr>
            <tr>
                <td class="label">Data e Hora:</td>
                <td>{{ $reuniao->data_hora?->format('d/m/Y \à\s H:i') ?? '—' }}</td>
                <td class="label">Status:</td>
                <td>{{ ucfirst($reuniao->status) }}</td>
            </tr>
            @if ($reuniao->local)
            <tr>
                <td class="label">Local:</td>
                <td colspan="3">{{ $reuniao->local }}</td>
            </tr>
            @endif
        </table>
    </div>

    {{-- Pauta --}}
    @if ($reuniao->pauta)
    <div class="pauta-section">
        <div class="section-title">Pauta / Ordem do Dia</div>
        <div class="pauta-content">{{ strip_tags($reuniao->pauta) }}</div>
    </div>
    @endif

    {{-- Tabela de Presença --}}
    <div class="presenca-section">
        <div class="section-title">Registro de Presença</div>
        <table class="presenca-table">
            <thead>
                <tr>
                    <th class="col-nome">Nome</th>
                    <th class="col-cargo">Cargo / Função</th>
                    <th class="col-entidade">Entidade / Segmento</th>
                    <th class="col-assinatura text-center">Assinatura</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($composicoes as $composicao)
                <tr>
                    <td>{{ $composicao->nome_exibicao }}</td>
                    <td>{{ match($composicao->tipo) {
                        'PRESIDENTE'      => 'Presidente',
                        'VICE_PRESIDENTE' => 'Vice-Presidente',
                        'SECRETARIO'      => 'Secretário(a)',
                        'MEMBRO'          => 'Membro',
                        'SUPLENTE'        => 'Suplente',
                        default           => $composicao->tipo,
                    } }}</td>
                    <td>{{ $composicao->conselheiro?->municipio?->nome ?? '—' }}</td>
                    <td class="assinatura-cell"></td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center">Nenhum membro na composição.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if ($composicoes->count() > 0)
        <div class="total-label">Total: {{ $composicoes->count() }} membro(s)</div>
        @endif
    </div>

    {{-- Visitantes --}}
    <div class="visitors-section">
        <div class="section-title">Visitantes / Observadores</div>
        @for ($i = 0; $i < 4; $i++)
        <div class="visitors-line"></div>
        @endfor
    </div>

    {{-- Rodapé --}}
    <div class="page-footer">
        Gerado em {{ now()->format('d/m/Y H:i') }} &nbsp;|&nbsp;
        {{ $reuniao->conselho->municipio->nome ?? '' }} &nbsp;|&nbsp;
        {{ $reuniao->conselho->nome ?? '' }}
    </div>

</div>
</body>
</html>
