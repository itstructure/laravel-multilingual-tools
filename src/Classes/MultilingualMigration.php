<?php
namespace Itstructure\Mult\Classes;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class MultilingualMigration
 *
 * @package Itstructure\Mult\Classes
 */
class MultilingualMigration extends Migration
{
    /**
     * Table name to contain the project languages.
     */
    const LANGUAGE_TABLE_NAME = 'languages';

    /**
     * Primary key name for any table in a system.
     */
    const PRIMARY_KEY_NAME = 'id';

    /**
     * Creates table with timestamp fields: created_at and updated_at.
     * @param string $tableName
     * @param callable $columnsCallback
     * @return void
     */
    public function createTableWithTimestamps(string $tableName, callable $columnsCallback): void
    {
        Schema::create($tableName, function (Blueprint $table) use ($columnsCallback) {
            $columnsCallback($table);
            $table->timestamps();
        });
    }

    /**
     * Creates two tables: main(primary) table and translate table.
     * For example:
     * pages:
     *  - id
     *  - created_at
     *  - updated_at
     *
     * pages_languages:
     *  - pages_id
     *  - languages_id
     *  - title
     *  - text
     *
     * @param string $tableName - table name which needs to be translated.
     * @param callable  $multilingualColumnsCallback - callback for multilingual fields.
     * @param callable|null  $primaryColumnsCallback - callback for main(primary) fields.
     *
     * @return void
     */
    public function createMultilingualTable(string $tableName, callable $multilingualColumnsCallback, callable $primaryColumnsCallback = null): void
    {
        $this->createTableWithTimestamps($tableName, function (Blueprint $table) use ($primaryColumnsCallback) {
            $table->bigIncrements(self::PRIMARY_KEY_NAME)->primaryKey();
            if (is_callable($primaryColumnsCallback)) {
                $primaryColumnsCallback($table);
            }
        });

        $keyToPrimaryTable = $this->keyToPrimaryTable($tableName);
        $keyToLanguageTable = self::keyToLanguageTable();

        $translateTableName = $this->translateTableName($tableName);

        $this->createTableWithTimestamps($translateTableName, function (Blueprint $table) use ($keyToPrimaryTable, $keyToLanguageTable, $multilingualColumnsCallback, $tableName) {
            $table->unsignedBigInteger($keyToPrimaryTable);
            $table->unsignedBigInteger($keyToLanguageTable);
            $table->primary([$keyToPrimaryTable, $keyToLanguageTable]);
            $multilingualColumnsCallback($table);

            $table->foreign($keyToPrimaryTable)->references(self::PRIMARY_KEY_NAME)->on($tableName)->onDelete('cascade');
            $table->foreign($keyToLanguageTable)->references(self::PRIMARY_KEY_NAME)->on(self::LANGUAGE_TABLE_NAME)->onDelete('cascade');
        });
    }

    /**
     * Drop main table with translate table.
     * @param string $tableName - main table name.
     * @return void
     */
    public function dropMultilingualTable(string $tableName): void
    {
        Schema::dropIfExists($this->translateTableName($tableName));
        Schema::dropIfExists($tableName);
    }

    /**
     * Returns key name for link translate table with languages table.
     * @return string
     */
    public static function keyToLanguageTable(): string
    {
        return self::LANGUAGE_TABLE_NAME . '_id';
    }

    /**
     * Returns table name for translates.
     * @param string $tableName - main(primary) table name.
     * @return string
     */
    private function translateTableName(string $tableName): string
    {
        return $tableName . '_' . self::LANGUAGE_TABLE_NAME;
    }

    /**
     * Returns key name for link translate table with main table.
     * @param string $tableName - main table name.
     * @return string
     */
    private function keyToPrimaryTable(string $tableName): string
    {
        return $tableName . '_id';
    }
}
