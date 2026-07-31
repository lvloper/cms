<?php

declare(strict_types=1);

use App\Models\Paco\ResponseBlock;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        ResponseBlock::query()->where('code', 'no_evidence_default')->update([
            'text' => 'Vamos a analizar cuál es el mejor enfoque para este caso y retomarlo con vos.',
        ]);
    }

    public function down(): void
    {
        ResponseBlock::query()->where('code', 'no_evidence_default')->update([
            'text' => 'No encontramos un caso publicable suficientemente parecido, pero podemos revisar la consulta con el equipo.',
        ]);
    }
};
