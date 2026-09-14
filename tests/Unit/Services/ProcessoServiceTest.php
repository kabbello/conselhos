<?php

namespace Tests\Unit\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Modules\Conselhos\Models\Conselho;
use Modules\Resolucoes\Models\Processo;
use Modules\Resolucoes\Services\ProcessoService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProcessoServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProcessoService $service;
    private Conselho $conselho;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service  = new ProcessoService();
        $this->conselho = Conselho::factory()->create();
    }

    #[Test]
    public function proximo_numero_inicia_em_001(): void
    {
        $numero = $this->service->proximoNumero($this->conselho, 2025);

        $this->assertEquals('001/2025', $numero);
    }

    #[Test]
    public function proximo_numero_incrementa_sequencialmente(): void
    {
        Processo::factory()->create([
            'conselho_id' => $this->conselho->id,
            'numero'      => '001/2025',
            'ano'         => 2025,
        ]);

        $numero = $this->service->proximoNumero($this->conselho, 2025);

        $this->assertEquals('002/2025', $numero);
    }

    #[Test]
    public function proximo_numero_reinicia_por_ano(): void
    {
        Processo::factory()->create([
            'conselho_id' => $this->conselho->id,
            'numero'      => '005/2024',
            'ano'         => 2024,
        ]);

        $numero = $this->service->proximoNumero($this->conselho, 2025);

        $this->assertEquals('001/2025', $numero);
    }

    #[Test]
    public function abrir_cria_processo_com_numero_automatico(): void
    {
        $processo = $this->service->abrir($this->conselho, [
            'titulo' => 'Aprovação do Plano Municipal',
            'tipo'   => 'DELIBERATIVO',
            'ano'    => 2025,
        ]);

        $this->assertNotNull($processo->numero);
        $this->assertStringEndsWith('/2025', $processo->numero);
        $this->assertEquals('ABERTO', $processo->status);
        $this->assertNotNull($processo->data_abertura);
    }

    #[Test]
    public function abrir_preserva_numero_informado_manualmente(): void
    {
        $processo = $this->service->abrir($this->conselho, [
            'titulo'  => 'Processo de Migração',
            'tipo'    => 'NORMATIVO',
            'ano'     => 2024,
            'numero'  => '099/2024',
        ]);

        $this->assertEquals('099/2024', $processo->numero);
    }

    #[Test]
    public function avancar_status_transicao_valida(): void
    {
        $processo = Processo::factory()->create([
            'conselho_id' => $this->conselho->id,
            'status'      => 'ABERTO',
        ]);

        $resultado = $this->service->avancarStatus($processo, 'EM_ANALISE');

        $this->assertEquals('EM_ANALISE', $resultado->status);
    }

    #[Test]
    public function avancar_status_votado_para_aprovado_define_data_encerramento(): void
    {
        $processo = Processo::factory()->create([
            'conselho_id' => $this->conselho->id,
            'status'      => 'VOTADO',
        ]);

        $resultado = $this->service->avancarStatus($processo, 'APROVADO');

        $this->assertEquals('APROVADO', $resultado->status);
        $this->assertNotNull($resultado->data_encerramento);
    }

    #[Test]
    public function avancar_status_transicao_invalida_lanca_excecao(): void
    {
        $processo = Processo::factory()->create([
            'conselho_id' => $this->conselho->id,
            'status'      => 'APROVADO',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/APROVADO/');

        $this->service->avancarStatus($processo, 'ABERTO');
    }

    #[Test]
    public function avancar_status_arquivado_e_status_terminal(): void
    {
        $processo = Processo::factory()->create([
            'conselho_id' => $this->conselho->id,
            'status'      => 'ARQUIVADO',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/terminal/');

        $this->service->avancarStatus($processo, 'ABERTO');
    }

    #[Test]
    public function aguardando_complementacao_retorna_para_em_analise(): void
    {
        $processo = Processo::factory()->create([
            'conselho_id' => $this->conselho->id,
            'status'      => 'AGUARDANDO_COMPLEMENTACAO',
        ]);

        $resultado = $this->service->avancarStatus($processo, 'EM_ANALISE');

        $this->assertEquals('EM_ANALISE', $resultado->status);
    }
}
