<?php

namespace App\Models;

/**
 * A02: alias para o modelo canônico em Modules\Municipios\Models\Municipio.
 * Mantém compatibilidade com referências existentes em app/ (Filament tenancy,
 * policies, providers) sem duplicar a implementação.
 */
class Municipio extends \Modules\Municipios\Models\Municipio
{
    //
}
