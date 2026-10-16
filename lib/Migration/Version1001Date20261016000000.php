<?php

declare(strict_types=1);

namespace OCA\WebRdp\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version1001Date20261016000000 extends SimpleMigrationStep {
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        $table = $schema->getTable('web_rdp_connections');
        if (!$table->hasColumn('keyboard_layout')) {
            $table->addColumn('keyboard_layout', 'string', [
                'notnull' => false,
                'length' => 32,
            ]);
            return $schema;
        }

        return null;
    }
}
