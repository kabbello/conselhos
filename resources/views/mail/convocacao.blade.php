<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>{{ $isUpdate ? 'Atualização de Convocação' : 'Convocação de Reunião' }}</title>
</head>
<body style="margin:0;padding:0;background:#f4f4f4;font-family:Arial,Helvetica,sans-serif;font-size:15px;">

@php
    $conselho   = $reuniao->conselho;
    $municipio  = $conselho->municipio ?? null;
    $tipo       = $reuniao->tipoReuniao->nome ?? 'Reunião';
    $sigla      = $conselho->sigla ?? '';
    $nomeC      = $conselho->nome ?? '';
    $dataPt     = $reuniao->data_hora?->format('d/m/Y') ?? '';
    $horaPt     = $reuniao->data_hora?->format('H:i') ?? '';
    $local      = $reuniao->local ?? '';
    $pauta      = $reuniao->pauta ?? '';
    $obs        = trim($reuniao->observacoes ?? '');
    $anexos     = $reuniao->anexos()->where('publicado', true)->get();
    $accentColor = $isUpdate ? '#e67e22' : '#27ae60';

    $labelCampo = [
        'data_hora'  => 'Data/Hora',
        'local'      => 'Local',
        'pauta'      => 'Pauta',
        'tipo_id'    => 'Tipo',
        'observacoes'=> 'Observações',
    ];
@endphp

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f4f4f4;padding:20px 0;">
  <tr>
    <td align="center">

      {{-- ── Container ── --}}
      <table width="600" cellpadding="0" cellspacing="0" border="0"
             style="background:#fff;border-radius:4px;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.12);">

        {{-- ── Cabeçalho ── --}}
        <tr>
          <td style="background:#fff;border-bottom:3px solid {{ $accentColor }};padding:20px;text-align:center;">
            <p style="margin:0 0 4px;font-size:13px;color:#888;text-transform:uppercase;letter-spacing:1px;">
              {{ $municipio ? 'Prefeitura Municipal de ' . $municipio->nome : '' }}
            </p>
            <p style="margin:0;font-size:18px;font-weight:bold;color:#222;">{{ $nomeC }}</p>
            <p style="margin:6px 0 0;font-size:13px;color:{{ $accentColor }};font-weight:bold;text-transform:uppercase;letter-spacing:.5px;">
              {{ $isUpdate ? '⚠ Atualização de Convocação' : '📣 Convocação de Reunião' }}
            </p>
          </td>
        </tr>

        {{-- ── Corpo ── --}}
        <tr>
          <td style="padding:28px 32px;color:#333;">

            <p style="margin:0 0 18px;">
              Olá, <strong>{{ $nomeDestinatario ?: 'conselheiro(a)' }}</strong>!
            </p>

            @if ($isUpdate)
              <p style="margin:0 0 6px;">
                Houve uma <strong>atualização</strong> na convocação da reunião <strong>{{ $tipo }}</strong>
                do <strong>{{ $sigla }}</strong>.
              </p>

              {{-- Alterações --}}
              @if (!empty($changes))
              <table width="100%" cellpadding="6" cellspacing="0" border="0"
                     style="border:1px solid #e0e0e0;border-radius:4px;margin:16px 0;font-size:13px;">
                <tr style="background:#fff8f0;">
                  <td colspan="3" style="padding:8px 12px;font-weight:bold;color:#e67e22;border-bottom:1px solid #e0e0e0;">
                    Alterações realizadas
                  </td>
                </tr>
                @foreach ($changes as $campo => $vals)
                <tr style="border-bottom:1px solid #f0f0f0;">
                  <td style="padding:6px 12px;font-weight:bold;color:#555;white-space:nowrap;width:110px;">
                    {{ $labelCampo[$campo] ?? $campo }}
                  </td>
                  <td style="padding:6px 8px;color:#c0392b;text-decoration:line-through;width:40%;">
                    @if ($campo === 'pauta')
                      {{ mb_strimwidth(strip_tags($vals['old'] ?? ''), 0, 80, '…') }}
                    @else
                      {{ $vals['old'] ?? '—' }}
                    @endif
                  </td>
                  <td style="padding:6px 8px;color:#27ae60;">
                    @if ($campo === 'pauta')
                      {{ mb_strimwidth(strip_tags($vals['new'] ?? ''), 0, 80, '…') }}
                    @else
                      {{ $vals['new'] ?? '—' }}
                    @endif
                  </td>
                </tr>
                @endforeach
              </table>
              @endif

              <p style="margin:0 0 6px;">Dados atuais da reunião:</p>
            @else
              <p style="margin:0 0 18px;">
                Você está convocado(a) para a reunião <strong>{{ $tipo }}</strong>
                do <strong>{{ $sigla }}</strong>.
              </p>
            @endif

            {{-- ── Dados da reunião ── --}}
            <table width="100%" cellpadding="0" cellspacing="0" border="0"
                   style="background:#f8f9fa;border-left:3px solid {{ $accentColor }};margin:0 0 20px;padding:0;">
              <tr>
                <td style="padding:14px 18px;">
                  <p style="margin:0 0 6px;font-size:14px;">
                    <strong>📅 Data:</strong>&nbsp;
                    {{ $dataPt }}{{ $horaPt ? ' às ' . $horaPt : '' }}
                  </p>
                  @if ($local)
                  <p style="margin:0;font-size:14px;">
                    <strong>📍 Local:</strong>&nbsp;{{ $local }}
                  </p>
                  @endif
                </td>
              </tr>
            </table>

            {{-- ── Pauta ── --}}
            @if ($pauta)
            <hr style="border:0;border-top:1px solid #e8e8e8;margin:18px 0"/>
            <p style="margin:0 0 8px;font-weight:bold;color:#444;">Pauta / Ordem do Dia</p>
            <div style="font-size:14px;line-height:1.6;color:#333;">
              {!! $pauta !!}
            </div>
            @endif

            {{-- ── Observações ── --}}
            @if ($obs)
            <hr style="border:0;border-top:1px solid #e8e8e8;margin:18px 0"/>
            <p style="margin:0 0 6px;font-weight:bold;color:#444;">Observações</p>
            <p style="margin:0;font-size:14px;color:#555;">{{ $obs }}</p>
            @endif

            {{-- ── Anexos ── --}}
            @if ($anexos->isNotEmpty())
            <hr style="border:0;border-top:1px solid #e8e8e8;margin:18px 0"/>
            <p style="margin:0 0 8px;font-weight:bold;color:#444;">📎 Anexos</p>
            <ul style="margin:0;padding-left:18px;font-size:14px;">
              @foreach ($anexos as $anexo)
              <li style="margin-bottom:4px;">
                @if ($anexo->arquivo_url)
                  <a href="{{ $anexo->arquivo_url }}" style="color:#2980b9;">
                    {{ $anexo->titulo ?: basename($anexo->arquivo_path) }}
                  </a>
                @else
                  {{ $anexo->titulo }}
                @endif
              </li>
              @endforeach
            </ul>
            @endif

            {{-- ── Assinatura ── --}}
            <hr style="border:0;border-top:1px solid #e8e8e8;margin:24px 0 16px"/>
            <p style="margin:0;font-size:13px;color:#666;">
              Obrigado,<br/>
              <strong>Secretaria {{ $sigla }}</strong><br/>
              {{ $nomeC }}
            </p>

          </td>
        </tr>

        {{-- ── Rodapé ── --}}
        <tr>
          <td style="background:#f8f9fa;border-top:1px solid #e8e8e8;padding:14px 32px;text-align:center;">
            <p style="margin:0;font-size:11px;color:#aaa;">
              Este é um aviso automático do Sistema de Conselhos Municipais.<br/>
              {{ $municipio ? $municipio->nome . ' — ' : '' }}{{ $nomeC }}
            </p>
          </td>
        </tr>

      </table>
      {{-- /Container --}}

    </td>
  </tr>
</table>

</body>
</html>
