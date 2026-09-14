<?php

namespace Tests\Unit\Services;

use Modules\Composicao\Models\Composicao;
use Modules\Composicao\Models\Conselheiro;
use Modules\Composicao\Services\ComposicaoService;
use Modules\Conselhos\Models\Conselho;
use Modules\Municipios\Models\Municipio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ComposicaoServiceTest extends TestCase
{
    use RefreshDatabase;

    private ComposicaoService $service;
    private Conselho $conselho;
    private Conselheiro $conselheiro;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ComposicaoService();

        $municipio = Municipio::factory()->create();

        $this->conselho = Conselho::factory()->create([
            'municipio_id' => $municipio->id,
        ]);

        $this->conselheiro = Conselheiro::factory()->create([
            'municipio_id' => $municipio->id,
        ]);
    }

    // ──────────────────────────────────────────────
    // RN-001 Adicionar membro
    // ──────────────────────────────────────────────

    #[Test]
    public function adicionar_cria_composicao_ativa(): void
    {
        $composicao = $this->service->adicionar($this->conselho, [
            'conselheiro_id' => $this->conselheiro->id,
            'tipo'           => 'MEMBRO',
        ]);

        $this->assertTrue($composicao->ativo);
        $this->assertEquals($this->conselho->id, $composicao->conselho_id);
        $this->assertEquals('MEMBRO', $composicao->tipo);
    }

    #[Test]
    public function adicionar_auto_preenche_nome_exibicao(): void
    {
        $composicao = $this->service->adicionar($this->conselho, [
            'conselheiro_id' => $this->conselheiro->id,
            'tipo'           => 'MEMBRO',
        ]);

        $this->assertEquals($this->conselheiro->nome, $composicao->nome_exibicao);
    }

    #[Test]
    public function adicionar_permite_membro_e_suplente_simultaneos(): void
    {
        $this->service->adicionar($this->conselho, [
            'conselheiro_id' => $this->conselheiro->id,
            'tipo'           => 'MEMBRO',
        ]);

        $suplente = $this->service->adicionar($this->conselho, [
            'conselheiro_id' => $this->conselheiro->id,
            'tipo'           => 'SUPLENTE',
        ]);

        $this->assertEquals('SUPLENTE', $suplente->tipo);
        $this->assertDatabaseCount('composicao', 2);
    }

    #[Test]
    public function adicionar_rejeita_tipo_duplicado_para_mesmo_conselheiro(): void
    {
        $this->service->adicionar($this->conselho, [
            'conselheiro_id' => $this->conselheiro->id,
            'tipo'           => 'MEMBRO',
        ]);

        $this->expectException(ValidationException::class);

        $this->service->adicionar($this->conselho, [
            'conselheiro_id' => $this->conselheiro->id,
            'tipo'           => 'MEMBRO',
        ]);
    }

    #[Test]
    public function adicionar_presidente_encerra_presidente_anterior(): void
    {
        $presidenteAntigo = Conselheiro::factory()->create([
            'municipio_id' => $this->conselho->municipio_id,
        ]);

        $this->service->adicionar($this->conselho, [
            'conselheiro_id' => $presidenteAntigo->id,
            'tipo'           => 'PRESIDENTE',
        ]);

        $novoPresidente = Conselheiro::factory()->create([
            'municipio_id' => $this->conselho->municipio_id,
        ]);

        $this->service->adicionar($this->conselho, [
            'conselheiro_id' => $novoPresidente->id,
            'tipo'           => 'PRESIDENTE',
        ]);

        // Apenas um PRESIDENTE ativo deve existir
        $ativos = Composicao::where('conselho_id', $this->conselho->id)
            ->where('tipo', 'PRESIDENTE')
            ->where('ativo', true)
            ->count();

        $this->assertEquals(1, $ativos);
        $this->assertEquals(
            $novoPresidente->id,
            Composicao::where('conselho_id', $this->conselho->id)
                ->where('tipo', 'PRESIDENTE')
                ->where('ativo', true)
                ->first()?->conselheiro_id
        );
    }

    #[Test]
    public function adicionar_sem_conselheiro_id_e_valido(): void
    {
        // ~40% dos registros legados não têm vínculo com login
        $composicao = $this->service->adicionar($this->conselho, [
            'conselheiro_id' => null,
            'tipo'           => 'MEMBRO',
            'nome_exibicao'  => 'João da Silva',
            'email_exibicao' => 'joao@example.com',
        ]);

        $this->assertNull($composicao->conselheiro_id);
        $this->assertEquals('João da Silva', $composicao->nome_exibicao);
    }

    // ──────────────────────────────────────────────
    // RN-002 Encerrar mandato
    // ──────────────────────────────────────────────

    #[Test]
    public function encerrar_marca_ativo_false_e_define_data_fim(): void
    {
        $composicao = $this->service->adicionar($this->conselho, [
            'conselheiro_id' => $this->conselheiro->id,
            'tipo'           => 'MEMBRO',
        ]);

        $this->service->encerrar($composicao, '2025-12-31', 'Fim de mandato');

        $this->assertFalse($composicao->fresh()->ativo);
        $this->assertEquals('2025-12-31', $composicao->fresh()->data_fim?->toDateString());
        $this->assertSoftDeleted('composicao', ['id' => $composicao->id]);
    }

    #[Test]
    public function encerrar_usa_data_atual_quando_nao_informada(): void
    {
        $composicao = $this->service->adicionar($this->conselho, [
            'conselheiro_id' => $this->conselheiro->id,
            'tipo'           => 'MEMBRO',
        ]);

        $this->service->encerrar($composicao);

        $this->assertEquals(
            now()->toDateString(),
            $composicao->fresh()->data_fim?->toDateString()
        );
    }

    #[Test]
    public function encerrar_mandato_ja_encerrado_lanca_excecao(): void
    {
        $composicao = $this->service->adicionar($this->conselho, [
            'conselheiro_id' => $this->conselheiro->id,
            'tipo'           => 'MEMBRO',
        ]);

        $this->service->encerrar($composicao);

        $this->expectException(ValidationException::class);
        $this->service->encerrar($composicao);
    }

    // ──────────────────────────────────────────────
    // RN-003 Promover membro
    // ──────────────────────────────────────────────

    #[Test]
    public function promover_altera_tipo_do_membro(): void
    {
        $composicao = $this->service->adicionar($this->conselho, [
            'conselheiro_id' => $this->conselheiro->id,
            'tipo'           => 'MEMBRO',
        ]);

        $this->service->promover($composicao, 'PRESIDENTE');

        $this->assertEquals('PRESIDENTE', $composicao->fresh()->tipo);
    }

    #[Test]
    public function promover_rebaixa_presidente_anterior_para_membro(): void
    {
        $presidenteAntigo = Conselheiro::factory()->create([
            'municipio_id' => $this->conselho->municipio_id,
        ]);

        $composicaoAntiga = $this->service->adicionar($this->conselho, [
            'conselheiro_id' => $presidenteAntigo->id,
            'tipo'           => 'PRESIDENTE',
        ]);

        $novoMembro = Conselheiro::factory()->create([
            'municipio_id' => $this->conselho->municipio_id,
        ]);

        $composicaoNova = $this->service->adicionar($this->conselho, [
            'conselheiro_id' => $novoMembro->id,
            'tipo'           => 'MEMBRO',
        ]);

        $this->service->promover($composicaoNova, 'PRESIDENTE');

        // Antigo presidente vira MEMBRO, ainda ativo
        $this->assertEquals('MEMBRO', $composicaoAntiga->fresh()->tipo);
        $this->assertTrue($composicaoAntiga->fresh()->ativo);

        // Novo PRESIDENTE ativo
        $this->assertEquals('PRESIDENTE', $composicaoNova->fresh()->tipo);
    }

    #[Test]
    public function promover_tipo_invalido_lanca_excecao(): void
    {
        $composicao = $this->service->adicionar($this->conselho, [
            'conselheiro_id' => $this->conselheiro->id,
            'tipo'           => 'MEMBRO',
        ]);

        $this->expectException(ValidationException::class);
        $this->service->promover($composicao, 'SUPLENTE');
    }

    #[Test]
    public function promover_membro_inativo_lanca_excecao(): void
    {
        $composicao = $this->service->adicionar($this->conselho, [
            'conselheiro_id' => $this->conselheiro->id,
            'tipo'           => 'MEMBRO',
        ]);

        $this->service->encerrar($composicao);

        $this->expectException(ValidationException::class);
        $this->service->promover($composicao, 'PRESIDENTE');
    }
}
