<?php

namespace Tests\Unit\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Conselhos\Models\Conselho;
use Modules\Resolucoes\Models\AtoNormativo;
use Modules\Resolucoes\Services\AtoNormativoService;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

class AtoNormativoServiceTest extends TestCase
{
    use RefreshDatabase;

    private AtoNormativoService $service;
    private Conselho $conselho;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service  = new AtoNormativoService();
        $this->conselho = Conselho::factory()->create();
    }

    #[Test]
    public function proximo_numero_inicia_em_1(): void
    {
        $numero = $this->service->proximoNumero($this->conselho, 'RESOLUCAO', 2025);

        $this->assertEquals(1, $numero);
    }

    #[Test]
    public function proximo_numero_sequencial_por_tipo(): void
    {
        AtoNormativo::factory()->create([
            'conselho_id' => $this->conselho->id,
            'tipo'        => 'RESOLUCAO',
            'numero'      => 3,
            'ano'         => 2025,
        ]);

        // RESOLUCAO deve ser 4
        $this->assertEquals(4, $this->service->proximoNumero($this->conselho, 'RESOLUCAO', 2025));

        // PORTARIA tem sequência própria: começa em 1
        $this->assertEquals(1, $this->service->proximoNumero($this->conselho, 'PORTARIA', 2025));
    }

    #[Test]
    public function proximo_numero_reinicia_por_ano(): void
    {
        AtoNormativo::factory()->create([
            'conselho_id' => $this->conselho->id,
            'tipo'        => 'RESOLUCAO',
            'numero'      => 10,
            'ano'         => 2024,
        ]);

        $numero = $this->service->proximoNumero($this->conselho, 'RESOLUCAO', 2025);

        $this->assertEquals(1, $numero);
    }

    #[Test]
    public function criar_gera_numero_automaticamente(): void
    {
        $ato = $this->service->criar($this->conselho, [
            'tipo'   => 'RESOLUCAO',
            'ano'    => 2025,
            'titulo' => 'Aprovação do PMAS 2025-2027',
            'ementa' => 'Aprova o Plano Municipal de Assistência Social.',
        ]);

        $this->assertEquals(1, $ato->numero);
        $this->assertEquals('RASCUNHO', $ato->status);
        $this->assertEquals('001/2025', $ato->numero_completo);
    }

    #[Test]
    public function publicar_ato_aprovado_muda_status_para_vigente(): void
    {
        $ato = AtoNormativo::factory()->aprovado()->create([
            'conselho_id' => $this->conselho->id,
        ]);

        $resultado = $this->service->publicar($ato, 'DOM nº 1234, de 10/01/2025');

        $this->assertTrue($resultado->publicado);
        $this->assertEquals('VIGENTE', $resultado->status);
        $this->assertEquals('DOM nº 1234, de 10/01/2025', $resultado->diario_oficial_referencia);
        $this->assertNotNull($resultado->data_publicacao);
    }

    #[Test]
    public function publicar_ato_em_rascunho_lanca_excecao(): void
    {
        $ato = AtoNormativo::factory()->create([
            'conselho_id' => $this->conselho->id,
            'status'      => 'RASCUNHO',
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/RASCUNHO/');

        $this->service->publicar($ato);
    }

    #[Test]
    public function revogar_ato_vigente_marca_revogado(): void
    {
        $atoAntigo = AtoNormativo::factory()->vigente()->create([
            'conselho_id' => $this->conselho->id,
        ]);

        // Cria primeiro sem revoga_id, depois faz update para acionar o hook saved + wasChanged
        $atoNovo = AtoNormativo::factory()->vigente()->create([
            'conselho_id' => $this->conselho->id,
        ]);
        $atoNovo->update(['revoga_id' => $atoAntigo->id]);

        // O boot do model (saved hook) deve ter atualizado o ato antigo
        $atoAntigo->refresh();

        $this->assertEquals('REVOGADO', $atoAntigo->status);
        $this->assertEquals($atoNovo->id, $atoAntigo->revogado_por_id);
    }

    #[Test]
    public function revogar_ato_nao_vigente_lanca_excecao(): void
    {
        $atoRevogado  = AtoNormativo::factory()->revogado()->create([
            'conselho_id' => $this->conselho->id,
        ]);
        $atoRevogador = AtoNormativo::factory()->vigente()->create([
            'conselho_id' => $this->conselho->id,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/VIGENTE/');

        $this->service->revogar($atoRevogado, $atoRevogador);
    }

    #[Test]
    public function suspender_ato_vigente(): void
    {
        $ato = AtoNormativo::factory()->vigente()->create([
            'conselho_id' => $this->conselho->id,
        ]);

        $resultado = $this->service->suspender($ato, 'Liminar judicial processo 1234/2025');

        $this->assertEquals('SUSPENSO', $resultado->status);
        $this->assertStringContainsString('SUSPENSO:', $resultado->observacoes);
    }

    #[Test]
    public function suspender_ato_nao_vigente_lanca_excecao(): void
    {
        $ato = AtoNormativo::factory()->create([
            'conselho_id' => $this->conselho->id,
            'status'      => 'RASCUNHO',
        ]);

        $this->expectException(RuntimeException::class);

        $this->service->suspender($ato, 'motivo');
    }
}
