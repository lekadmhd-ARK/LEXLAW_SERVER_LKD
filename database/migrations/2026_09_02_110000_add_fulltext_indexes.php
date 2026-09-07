<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getConfig("driver");

        if ($driver === "pgsql") {
            DB::statement("CREATE INDEX ft_regulations_title ON regulations USING GIN (to_tsvector('english', coalesce(title, '') || ' ' || coalesce(description, '')))");
            DB::statement("CREATE INDEX ft_regulation_contents_content ON regulation_contents USING GIN (to_tsvector('english', coalesce(content, '')))");
            DB::statement("CREATE INDEX ft_legal_glossaries_term ON legal_glossaries USING GIN (to_tsvector('english', coalesce(term, '') || ' ' || coalesce(definition, '')))");
        } elseif ($driver === "mysql") {
            DB::statement("ALTER TABLE regulations ADD FULLTEXT INDEX ft_regulations_title (title, description)");
            DB::statement("ALTER TABLE regulation_contents ADD FULLTEXT INDEX ft_regulation_contents_content (content)");
            DB::statement("ALTER TABLE legal_glossaries ADD FULLTEXT INDEX ft_legal_glossaries_term (term, definition)");
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getConfig("driver");

        if ($driver === "pgsql") {
            DB::statement("DROP INDEX IF EXISTS ft_regulations_title");
            DB::statement("DROP INDEX IF EXISTS ft_regulation_contents_content");
            DB::statement("DROP INDEX IF EXISTS ft_legal_glossaries_term");
        } elseif ($driver === "mysql") {
            DB::statement("DROP INDEX ft_regulations_title ON regulations");
            DB::statement("DROP INDEX ft_regulation_contents_content ON regulation_contents");
            DB::statement("DROP INDEX ft_legal_glossaries_term ON legal_glossaries");
        }
    }
};
