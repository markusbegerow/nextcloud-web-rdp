<?php

declare(strict_types=1);

namespace OCA\WebRdp\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version1000Date20260517000000 extends SimpleMigrationStep {
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        if (!$schema->hasTable('web_rdp_connections')) {
            $table = $schema->createTable('web_rdp_connections');

            $table->addColumn('id', 'integer', [
                'autoincrement' => true,
                'notnull' => true,
                'unsigned' => true,
            ]);
            $table->addColumn('user_id', 'string', [
                'notnull' => true,
                'length' => 64,
            ]);
            $table->addColumn('name', 'string', [
                'notnull' => true,
                'length' => 128,
            ]);
            $table->addColumn('host', 'string', [
                'notnull' => true,
                'length' => 255,
            ]);
            $table->addColumn('port', 'integer', [
                'notnull' => true,
                'default' => 3389,
            ]);
            $table->addColumn('username', 'string', [
                'notnull' => true,
                'length' => 128,
            ]);
            $table->addColumn('password', 'text', [
                'notnull' => true,
            ]);
            $table->addColumn('domain', 'string', [
                'notnull' => false,
                'length' => 128,
            ]);
            $table->addColumn('width', 'integer', [
                'notnull' => true,
                'default' => 1920,
            ]);
            $table->addColumn('height', 'integer', [
                'notnull' => true,
                'default' => 1080,
            ]);
            $table->addColumn('created_at', 'datetime', [
                'notnull' => true,
            ]);

            $table->setPrimaryKey(['id']);
            $table->addIndex(['user_id'], 'web_rdp_connections_user_id');
        }

        return $schema;
    }
}
