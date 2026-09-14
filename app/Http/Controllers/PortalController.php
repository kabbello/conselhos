<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Municipios\Models\Municipio;
use Modules\Conselhos\Models\Conselho;

class PortalController extends Controller
{
    // ── Helpers de resolução ──────────────────────────────────────────────────

    private function resolveMunicipio(string $slug): Municipio
    {
        return Municipio::where('slug', $slug)->where('ativo', true)->firstOrFail();
    }

    private function resolveConselho(Municipio $municipio, string $slug): Conselho
    {
        return Conselho::where('municipio_id', $municipio->id)
            ->where('slug', $slug)
            ->where('ativo', true)
            ->firstOrFail();
    }

    // ── Página inicial do portal ──────────────────────────────────────────────

    public function index(string $municipio)
    {
        $municipio = $this->resolveMunicipio($municipio);

        $conselhos = $municipio->conselhos()
            ->where('ativo', true)
            ->orderBy('nome')
            ->withCount([
                'membrosAtivos',
                'reunioes as reunioes_agendadas_count' => fn ($q) => $q->where('status', 'agendada'),
                'atosNormativos as atos_vigentes_count' => fn ($q) => $q->where('publicado', true)->where('status', 'VIGENTE'),
            ])
            ->get();

        return view('portal.index', compact('municipio', 'conselhos'));
    }

    // ── Página do conselho ────────────────────────────────────────────────────

    public function conselho(string $municipio, string $conselho)
    {
        $municipio = $this->resolveMunicipio($municipio);
        $conselho  = $this->resolveConselho($municipio, $conselho);

        $gestores = $conselho->composicao()
            ->with('conselheiro')
            ->whereIn('tipo', ['PRESIDENTE', 'VICE_PRESIDENTE', 'SECRETARIO'])
            ->where('ativo', true)->whereNull('deleted_at')
            ->orderByRaw("CASE tipo WHEN 'PRESIDENTE' THEN 1 WHEN 'VICE_PRESIDENTE' THEN 2 WHEN 'SECRETARIO' THEN 3 ELSE 4 END")
            ->get();

        $membros = $conselho->composicao()
            ->with('conselheiro')
            ->where('ativo', true)->whereNull('deleted_at')
            ->orderBy('tipo')->orderBy('nome_exibicao')
            ->get();

        $reunioesAgendadas = $conselho->reunioes()
            ->with(['tipoReuniao', 'links' => fn ($q) => $q->where('audiencia_publica', true)])
            ->where('status', 'agendada')->where('data_hora', '>=', now())
            ->orderBy('data_hora')->limit(5)->get();

        $reunioesRealizadas = $conselho->reunioes()
            ->with(['tipoReuniao', 'links' => fn ($q) => $q->where('audiencia_publica', true)])
            ->where('status', 'realizada')
            ->orderByDesc('data_hora')->limit(5)->get();

        $totalReunioesRealizadas = $conselho->reunioes()->where('status', 'realizada')->count();

        $atosNormativos = $conselho->atosNormativos()
            ->where('publicado', true)->whereIn('status', ['VIGENTE', 'APROVADO'])
            ->orderByDesc('data_publicacao')->orderByDesc('numero')
            ->limit(5)->get();

        $totalAtosNormativos = $conselho->atosNormativos()
            ->where('publicado', true)->whereIn('status', ['VIGENTE', 'APROVADO'])->count();

        $documentos = $conselho->documentos()
            ->with('tipoDocumento')->where('publico', true)->whereNull('deleted_at')
            ->orderByDesc('data_publicacao')->orderByDesc('updated_at')
            ->limit(5)->get();

        $totalDocumentos = $conselho->documentos()->where('publico', true)->whereNull('deleted_at')->count();

        $legislacao = $conselho->legislacoes()
            ->with('tipoLegislacao')->where('publico', true)
            ->orderBy('numero')->limit(5)->get();

        $totalLegislacao = $conselho->legislacoes()->where('publico', true)->count();

        return view('portal.conselho', compact(
            'municipio', 'conselho', 'gestores', 'membros',
            'reunioesAgendadas', 'reunioesRealizadas', 'totalReunioesRealizadas',
            'atosNormativos', 'totalAtosNormativos',
            'documentos', 'totalDocumentos',
            'legislacao', 'totalLegislacao',
        ));
    }

    // ── Documentos paginados ──────────────────────────────────────────────────

    public function documentos(Request $request, string $municipio, string $conselho)
    {
        $municipio = $this->resolveMunicipio($municipio);
        $conselho  = $this->resolveConselho($municipio, $conselho);

        $query = $conselho->documentos()
            ->with('tipoDocumento')
            ->where('publico', true)
            ->whereNull('deleted_at');

        if ($busca = $request->get('busca')) {
            $query->where(fn ($q) => $q
                ->where('titulo', 'like', "%{$busca}%")
                ->orWhere('descricao', 'like', "%{$busca}%")
            );
        }

        if ($tipo = $request->get('tipo')) {
            $query->where('tipo_documento_id', $tipo);
        }

        $documentos  = $query->orderByDesc('data_documento')->paginate(10)->withQueryString();
        $tipos       = $conselho->documentos()->with('tipoDocumento')->where('publico', true)
                        ->get()->pluck('tipoDocumento')->filter()->unique('id')->sortBy('nome');

        $titulo = 'Documentos';
        $secao  = 'documentos';

        return view('portal.lista', compact('municipio', 'conselho', 'documentos', 'tipos', 'titulo', 'secao', 'busca'));
    }

    // ── Legislação paginada ───────────────────────────────────────────────────

    public function legislacao(Request $request, string $municipio, string $conselho)
    {
        $municipio = $this->resolveMunicipio($municipio);
        $conselho  = $this->resolveConselho($municipio, $conselho);

        $query = $conselho->legislacoes()
            ->with('tipoLegislacao')
            ->where('publico', true);

        if ($busca = $request->get('busca')) {
            $query->where(fn ($q) => $q
                ->where('titulo', 'like', "%{$busca}%")
                ->orWhere('numero', 'like', "%{$busca}%")
            );
        }

        if ($tipo = $request->get('tipo')) {
            $query->where('tipo_legislacao_id', $tipo);
        }

        $legislacao = $query->orderBy('numero')->paginate(10)->withQueryString();
        $tipos      = $conselho->legislacoes()->with('tipoLegislacao')->where('publico', true)
                        ->get()->pluck('tipoLegislacao')->filter()->unique('id')->sortBy('nome');

        $titulo = 'Legislação';
        $secao  = 'legislacao';

        return view('portal.lista', compact('municipio', 'conselho', 'legislacao', 'tipos', 'titulo', 'secao', 'busca'));
    }

    // ── Reuniões paginadas ────────────────────────────────────────────────────

    public function reunioes(Request $request, string $municipio, string $conselho)
    {
        $municipio = $this->resolveMunicipio($municipio);
        $conselho  = $this->resolveConselho($municipio, $conselho);

        $query = $conselho->reunioes()
            ->with(['tipoReuniao', 'links' => fn ($q) => $q->where('audiencia_publica', true)]);

        if ($busca = $request->get('busca')) {
            $query->where(fn ($q) => $q
                ->where('pauta', 'like', "%{$busca}%")
                ->orWhere('local', 'like', "%{$busca}%")
            );
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $reunioes = $query->orderByDesc('data_hora')->paginate(10)->withQueryString();

        $titulo = 'Reuniões';
        $secao  = 'reunioes';

        return view('portal.lista', compact('municipio', 'conselho', 'reunioes', 'titulo', 'secao', 'busca', 'status'));
    }

    // ── Atos Normativos paginados ─────────────────────────────────────────────

    public function atosNormativos(Request $request, string $municipio, string $conselho)
    {
        $municipio = $this->resolveMunicipio($municipio);
        $conselho  = $this->resolveConselho($municipio, $conselho);

        $query = $conselho->atosNormativos()
            ->where('publicado', true);

        if ($busca = $request->get('busca')) {
            $query->where(fn ($q) => $q
                ->where('titulo', 'like', "%{$busca}%")
                ->orWhere('ementa', 'like', "%{$busca}%")
                ->orWhere('numero_completo', 'like', "%{$busca}%")
            );
        }

        if ($tipo = $request->get('tipo')) {
            $query->where('tipo', $tipo);
        }

        if ($statusFiltro = $request->get('status')) {
            $query->where('status', $statusFiltro);
        }

        $atosNormativos = $query->orderByDesc('ano')->orderByDesc('numero')->paginate(10)->withQueryString();

        $titulo = 'Atos Normativos';
        $secao  = 'atos-normativos';

        return view('portal.lista', compact('municipio', 'conselho', 'atosNormativos', 'titulo', 'secao', 'busca'));
    }
}
