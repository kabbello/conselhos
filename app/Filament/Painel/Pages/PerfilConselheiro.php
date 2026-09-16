<?php

namespace App\Filament\Painel\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile;
use Modules\Composicao\Models\Conselheiro;

/**
 * Página de perfil do painel municipal.
 * Para usuários vinculados a um Conselheiro, exibe também campos de
 * foto e telefone salvos na tabela conselheiros.
 */
class PerfilConselheiro extends EditProfile
{
    public function form(Form $form): Form
    {
        $user        = auth()->user();
        $conselheiro = $user?->conselheiro;

        $schema = [
            Forms\Components\Section::make('Dados de acesso')->schema([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ])->columns(2),
        ];

        if ($conselheiro) {
            $schema[] = Forms\Components\Section::make('Perfil do conselheiro')
                ->description('Informações exibidas publicamente no portal do município.')
                ->schema([
                    Forms\Components\FileUpload::make('conselheiro_foto')
                        ->label('Foto')
                        ->image()
                        ->disk('r2')
                        ->directory('conselheiros/fotos')
                        ->visibility('public')
                        ->maxSize(4096)
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->helperText('JPG, PNG ou WEBP. Máx. 4 MB. Recomendado: quadrada, mínimo 200×200 px.')
                        ->imagePreviewHeight('140')
                        ->imageEditor()
                        ->imageEditorAspectRatios(['1:1'])
                        ->default($conselheiro->foto_path)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('conselheiro_telefone')
                        ->label('Telefone')
                        ->tel()
                        ->maxLength(20)
                        ->default($conselheiro->telefone),

                    Forms\Components\TextInput::make('conselheiro_cpf')
                        ->label('CPF')
                        ->default($conselheiro->cpf)
                        ->disabled()
                        ->helperText('O CPF não pode ser alterado. Entre em contato com o administrador municipal.'),
                ])->columns(2);
        }

        return $form->schema($schema);
    }

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        $conselheiro     = $record->conselheiro;
        $trocouSenha     = filled($data['password'] ?? null);

        // Extrai e remove campos do conselheiro antes de salvar o User
        $foto     = $data['conselheiro_foto'] ?? null;
        $telefone = $data['conselheiro_telefone'] ?? null;
        unset($data['conselheiro_foto'], $data['conselheiro_telefone'], $data['conselheiro_cpf']);

        // Se trocou a senha, limpa o flag de reset obrigatório
        if ($trocouSenha) {
            $data['must_reset_password'] = false;
        }

        $record = parent::handleRecordUpdate($record, $data);

        // Salva campos do conselheiro separadamente
        if ($conselheiro) {
            $updates = array_filter([
                'foto_path' => $foto,
                'telefone'  => $telefone,
            ], fn ($v) => $v !== null);

            if ($updates) {
                $conselheiro->update($updates);

                activity('perfil-conselheiro')
                    ->causedBy($record)
                    ->performedOn($conselheiro)
                    ->withProperties($updates)
                    ->event('perfil_atualizado')
                    ->log('Conselheiro atualizou o próprio perfil');
            }
        }

        return $record;
    }

    protected function getRedirectUrl(): string
    {
        $user      = auth()->user();
        $municipio = $user->municipio;

        if ($municipio) {
            return url("/painel/municipio/{$municipio->slug}");
        }

        // super_admin ou usuário sem município — vai para o painel raiz
        return url('/painel');
    }
}
