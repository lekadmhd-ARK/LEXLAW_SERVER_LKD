<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE regulations ADD FULLTEXT INDEX ft_regulations_title (title, description)');
        DB::statement('ALTER TABLE regulation_contents ADD FULLTEXT INDEX ft_regulation_contents_content (content)');
        DB::statement('ALTER TABLE legal_glossaries ADD FULLTEXT INDEX ft_legal_glossaries_term (term, definition)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX ft_regulations_title ON regulations');
        DB::statement('DROP INDEX ft_regulation_contents_content ON regulation_contents');
        DB::statement('DROP INDEX ft_legal_glossaries_term ON legal_glossaries');
    }
};
