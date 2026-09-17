<!DOCTYPE html>
<html lang="pt-BR">
<head><meta charset="UTF-8"><style>
    body { font-family: sans-serif; font-size: 14px; color: #1e293b; background: #f8fafc; margin: 0; padding: 0; }
    .wrap { max-width: 600px; margin: 32px auto; background: #fff; border-radius: 8px; border: 1px solid #e2e8f0; overflow: hidden; }
    .header { background: #1d4ed8; color: #fff; padding: 24px 32px; }
    .header h1 { margin: 0; font-size: 18px; font-weight: 700; }
    .header p { margin: 4px 0 0; font-size: 13px; opacity: .8; }
    .body { padding: 28px 32px; }
    .field { margin-bottom: 20px; }
    .label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #64748b; margin-bottom: 4px; }
    .value { background: #f1f5f9; border-radius: 6px; padding: 10px 14px; font-size: 14px; color: #0f172a; word-break: break-word; white-space: pre-wrap; }
    .footer { background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 16px 32px; font-size: 12px; color: #94a3b8; }
</style></head>
<body>
<div class="wrap">
    <div class="header">
        <h1>⚠ Relato de erro — Portal dos Conselhos</h1>
        <p>{{ $municipio }}</p>
    </div>
    <div class="body">

        @if($nomeRemetente || $emailRemetente)
        <div class="field">
            <div class="label">Enviado por</div>
            <div class="value">{{ $nomeRemetente ?: '(não informado)' }}{{ $emailRemetente ? ' &lt;' . $emailRemetente . '&gt;' : '' }}</div>
        </div>
        @endif

        <div class="field">
            <div class="label">Página com erro</div>
            <div class="value">{{ $pagina }}</div>
        </div>

        <div class="field">
            <div class="label">Descrição do problema</div>
            <div class="value">{{ $descricao }}</div>
        </div>

    </div>
    <div class="footer">
        Mensagem automática gerada pelo Portal dos Conselhos Municipais · {{ now()->format('d/m/Y H:i') }}
    </div>
</div>
</body>
</html>
