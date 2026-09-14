<?php

namespace Modules\LGPD\Filament\Resources;

use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\LGPD\Filament\Resources\RegistroTratamentoResource\Pages;
use Modules\LGPD\Models\RegistroTratamento;

class RegistroTratamentoResource extends Resource
{
    protected static ?string $model = RegistroTratamento::class;
    protected static bool $isScopedToTenant = false;
    protected static ?string $navigationIcon   = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup  = 'LGPD';
    protected static ?string $navigationLabel  = 'Registro de Tratamento';
    protected static ?string $modelLabel       = 'Registro de tratamento';
    protected static ?string $pluralModelLabel = 'Registros de tratamento';
    protected static ?int    $navigationSort   = 2;

    public static function canAccess(): bool
    {
        $user = Filament::auth()->user();
        return $user && $user->hasAnyRole(['encarregado_dados', 'admin_municipal', 'super_admin']);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('municipio_id', Filament::getTenant()->id);
    }

    public static function form(Form $form): Form
    {
        $basesLegais = collect([
            'consentimento', 'contrato', 'obrigacao_legal', 'exercicio_regular_direitos',
            'protecao_vida', 'tutela_saude', 'interesse_legitimo', 'protecao_credito', 'politica_publica',
        ])->mapWithKeys(fn ($v) => [$v => RegistroTratamento::baseLegalLabel($v)]);

        $basesLegaisSensiveis = collect([
            'tutela_saude', 'obrigacao_legal', 'politica_publica',
            'exercicio_regular_direitos', 'prevencao_fraude',
        ])->mapWithKeys(fn ($v) => [$v => RegistroTratamento::baseLegalSensiveisLabel($v)]);

        return $form->schema([
            Forms\Components\Section::make('Responsável e sistemas')->schema([
                Forms\Components\TextInput::make('controlador')
                    ->label('Controlador responsável')
                    ->placeholder('Ex.: Prefeitura Municipal de ...')
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('operadores')
                    ->label('Operadores / suboperadores')
                    ->rows(2)
                    ->placeholder('Empresas ou serviços que tratam os dados em nome do controlador')
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('sistemas')
                    ->label('Sistemas e bases de dados envolvidos')
                    ->rows(2)
                    ->placeholder('Ex.: Sistema de Conselhos, e-mail municipal, planilha legada')
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('origem_dados')
                    ->label('Origem / fonte dos dados')
                    ->rows(2)
                    ->placeholder('Ex.: formulário de cadastro, importação do sistema legado')
                    ->columnSpanFull(),
            ])->columns(1)->collapsible(),

            Forms\Components\Section::make('Identificação da atividade')->schema([
                Forms\Components\TextInput::make('atividade')
                    ->label('Nome da atividade de tratamento')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Ex.: Cadastro de conselheiros')
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('finalidade')
                    ->label('Finalidade')
                    ->required()
                    ->rows(3)
                    ->placeholder('Para quê os dados são coletados e tratados')
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('dados_pessoais')
                    ->label('Dados pessoais tratados')
                    ->required()
                    ->rows(3)
                    ->placeholder('Ex.: nome, CPF, endereço, telefone, e-mail')
                    ->columnSpanFull(),

                Forms\Components\Toggle::make('dados_sensiveis')
                    ->label('Contém dados sensíveis (Art. 11)?')
                    ->default(false)
                    ->live()
                    ->inline(false),

                Forms\Components\Select::make('base_legal_sensiveis')
                    ->label('Base legal — dados sensíveis (Art. 11)')
                    ->options($basesLegaisSensiveis)
                    ->nullable()
                    ->visible(fn (Forms\Get $get) => $get('dados_sensiveis')),

                Forms\Components\TextInput::make('categorias_titulares')
                    ->label('Categorias de titulares')
                    ->required()
                    ->default('Conselheiros')
                    ->placeholder('Ex.: Conselheiros, servidores, cidadãos'),

                Forms\Components\Toggle::make('envolve_criancas')
                    ->label('Envolve dados de crianças ou adolescentes?')
                    ->default(false)
                    ->inline(false),
            ])->columns(2),

            Forms\Components\Section::make('Base legal e retenção')->schema([
                Forms\Components\Select::make('base_legal')
                    ->label('Base legal (LGPD, Art. 7º)')
                    ->required()
                    ->options($basesLegais),

                Forms\Components\TextInput::make('prazo_retencao')
                    ->label('Prazo de retenção')
                    ->required()
                    ->placeholder('Ex.: 5 anos após fim do mandato'),

                Forms\Components\Textarea::make('metodo_descarte')
                    ->label('Método de descarte ao fim do prazo')
                    ->rows(2)
                    ->placeholder('Ex.: exclusão lógica + relatório de eliminação')
                    ->columnSpanFull(),

                Forms\Components\Toggle::make('transferencia_internacional')
                    ->label('Há transferência internacional de dados?')
                    ->default(false)
                    ->inline(false),

                Forms\Components\Toggle::make('ativo')
                    ->label('Tratamento ativo')
                    ->default(true)
                    ->inline(false),
            ])->columns(2),

            Forms\Components\Section::make('Compartilhamento e segurança')
                ->description('Opcional — preencha se aplicável.')
                ->schema([
                    Forms\Components\Textarea::make('destinatarios')
                        ->label('Destinatários / com quem os dados são compartilhados')
                        ->rows(3)
                        ->placeholder('Ex.: Secretaria Municipal de Saúde, TCE/SP'),

                    Forms\Components\Textarea::make('medidas_seguranca')
                        ->label('Medidas de segurança adotadas')
                        ->rows(3)
                        ->placeholder('Ex.: criptografia em repouso, controle de acesso por papel'),
                ])->columns(2),

            Forms\Components\Section::make('Revisão e RIPD')
                ->description('Governança e conformidade.')
                ->schema([
                    Forms\Components\TextInput::make('responsavel_revisao')
                        ->label('Responsável pela próxima revisão')
                        ->placeholder('Nome ou cargo'),

                    Forms\Components\DatePicker::make('data_proxima_revisao')
                        ->label('Data da próxima revisão')
                        ->native(false)
                        ->displayFormat('d/m/Y'),

                    Forms\Components\Textarea::make('observacoes_ripd')
                        ->label('RIPD associado / observações')
                        ->rows(2)
                        ->columnSpanFull()
                        ->placeholder('Referência ao Relatório de Impacto se aplicável'),
                ])->columns(2)->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('atividade')
                    ->label('Atividade')
                    ->searchable()
                    ->weight('semibold')
                    ->limit(40),

                Tables\Columns\TextColumn::make('base_legal')
                    ->label('Base legal')
                    ->badge()
                    ->formatStateUsing(fn ($state) => RegistroTratamento::baseLegalLabel($state))
                    ->color('info'),

                Tables\Columns\TextColumn::make('categorias_titulares')
                    ->label('Titulares')
                    ->limit(30),

                Tables\Columns\TextColumn::make('prazo_retencao')
                    ->label('Retenção')
                    ->limit(30),

                Tables\Columns\IconColumn::make('dados_sensiveis')
                    ->label('Sensível')
                    ->boolean()
                    ->trueColor('danger')
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('envolve_criancas')
                    ->label('Crianças')
                    ->boolean()
                    ->trueColor('warning')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('data_proxima_revisao')
                    ->label('Revisão')
                    ->date('d/m/Y')
                    ->color(fn ($state) => $state && \Carbon\Carbon::parse($state)->isPast() ? 'danger' : null)
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('transferencia_internacional')
                    ->label('Trans. int.')
                    ->boolean()
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('ativo')
                    ->label('Ativo')
                    ->boolean()
                    ->alignCenter(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('ativo')
                    ->label('Situação')
                    ->placeholder('Todos')
                    ->trueLabel('Ativos')
                    ->falseLabel('Encerrados')
                    ->default(true),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('atividade');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListRegistroTratamentos::route('/'),
            'create' => Pages\CreateRegistroTratamento::route('/create'),
            'edit'   => Pages\EditRegistroTratamento::route('/{record}/edit'),
        ];
    }
}
