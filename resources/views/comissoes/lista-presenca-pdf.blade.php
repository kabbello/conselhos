<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Presença — {{ $reuniao->comissao->nome ?? '' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            color: #000;
            background: #fff;
        }

        .page {
            width: 100%;
            padding: 15mm 15mm 20mm 15mm;
        }

        /* Cabeçalho */
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }

        .header .municipio-nome {
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .header .conselho-nome {
            font-size: 11pt;
            font-weight: bold;
            margin-top: 3px;
            color: #333;
        }

        .header .comissao-nome {
            font-size: 12pt;
            font-weight: bold;
            margin-top: 4px;
        }

        .header .doc-titulo {
            font-size: 10pt;
            margin-top: 6px;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #333;
        }

        /* Caixa de informações da reunião */
        .info-box {
            border: 1px solid #000;
            padding: 8px 12px;
            margin-bottom: 12px;
        }

        .info-box table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-box td {
            padding: 3px 6px;
            font-size: 10pt;
            vertical-align: top;
        }

        .info-box td.label {
            font-weight: bold;
            white-space: nowrap;
            width: 140px;
        }

        /* Seção de pauta */
        .pauta-section {
            margin-bottom: 14px;
        }

        .section-title {
            font-size: 10pt;
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 1px solid #000;
            padding-bottom: 3px;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
        }

        .pauta-content {
            font-size: 10pt;
            line-height: 1.5;
            padding: 4px 0;
            white-space: pre-wrap;
        }

        /* Tabela de presença */
        .presenca-section {
            margin-bottom: 16px;
        }

        .presenca-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5pt;
        }

        .presenca-table th {
            background-color: #e8e8e8;
            border: 1px solid #000;
            padding: 5px 6px;
            text-align: left;
            font-weight: bold;
            font-size: 9pt;
        }

        .presenca-table td {
            border: 1px solid #000;
            padding: 5px 6px;
            vertical-align: middle;
        }

        .presenca-table tr:nth-child(even) td {
            background-color: #f9f9f9;
        }

        .col-nome       { width: 35%; }
        .col-papel      { width: 20%; }
        .col-assinatura { width: 45%; }

        .assinatura-cell {
            height: 28px;
        }

        /* Rodapé / Visitantes */
        .visitors-section {
            margin-top: 18px;
            border-top: 1px dashed #555;
            padding-top: 10px;
        }

        .visitors-section .section-title {
            border-bottom: none;
            margin-bottom: 8px;
        }

        .visitors-line {
            border-bottom: 1px solid #aaa;
            height: 24px;
            margin-bottom: 4px;
        }

        /* Rodapé da página */
        .page-footer {
            margin-top: 20px;
            border-top: 1px solid #ccc;
            padding-top: 6px;
            font-size: 8pt;
            color: #555;
            text-align: center;
        }

        .text-center { text-align: center; }
        .mt-4        { margin-top: 4px; }
    </style>
</head>
<body>
<div class="page">

    {{-- Cabeçalho --}}
    <div class="header">
        <div class="municipio-nome">
            Prefeitura Municipal de {{ $reuniao->comissao->conselho->municipio->nome ?? '' }}
        </div>
        <div class="conselho-nome">
            {{ $reuniao->comissao->conselho->nome ?? '' }}
        </div>
        <div class="comissao-nome">
            {{ $reuniao->comissao->nome ?? '' }}
        </div>
        <div class="doc-titulo">Lista de Presença — Reunião de Comissão</div>
    </div>

    {{-- Informações da Reunião --}}
    <div class="info-box">
        <table>
            <tr>
                <td class="label">Nº da Reunião:</td>
                <td>{{ $reuniao->numero ? '#' . $reuniao->numero : '—' }}</td>
                <td class="label">Tipo:</td>
                <td>
                    {{ match($reuniao->tipo) {
                        'ORDINARIA'      => 'Ordinária',
                        'EXTRAORDINARIA' => 'Extraordinária',
                        default          => $reuniao->tipo,
                    } }}
                </td>
            </tr>
            <tr>
                <td class="label">Data e Hora:</td>
                <td>{{ $reuniao->data_hora ? $reuniao->data_hora->format('d/m/Y \à\s H:i') : '—' }}</td>
                <td class="label">Status:</td>
                <td>
                    {{ match($reuniao->status) {
                        'AGENDADA'  => 'Agendada',
                        'REALIZADA' => 'Realizada',
                        'CANCELADA' => 'Cancelada',
                        default     => $reuniao->status,
                    } }}
                </td>
            </tr>
            <tr>
                <td class="label">Local:</td>
                <td colspan="3">{{ $reuniao->local ?? '—' }}</td>
            </tr>
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
        <div class="section-title">Registro de Presença — Membros da Comissão</div>
        <table class="presenca-table">
            <thead>
                <tr>
                    <th class="col-nome">Nome</th>
                    <th class="col-papel">Papel na Comissão</th>
                    <th class="col-assinatura text-center">Assinatura</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($membros as $membro)
                <tr>
                    <td>{{ $membro->nome }}</td>
                    <td>{{ $membro->papel ?? '—' }}</td>
                    <td class="assinatura-cell"></td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center">Nenhum membro ativo nesta comissão.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if ($membros->count() > 0)
        <div class="mt-4" style="font-size: 8.5pt; color: #555;">
            Total de membros ativos: {{ $membros->count() }}
        </div>
        @endif
    </div>

    {{-- Visitantes / Observadores --}}
    <div class="visitors-section">
        <div class="section-title">Visitantes / Observadores</div>
        @for ($i = 0; $i < 5; $i++)
        <div class="visitors-line"></div>
        @endfor
    </div>

    {{-- Rodapé --}}
    <div class="page-footer">
        Documento gerado em {{ now()->format('d/m/Y \à\s H:i') }} &nbsp;|&nbsp;
        {{ $reuniao->comissao->conselho->municipio->nome ?? '' }} &nbsp;|&nbsp;
        {{ $reuniao->comissao->nome ?? '' }}
    </div>

</div>
</body>
</html>
