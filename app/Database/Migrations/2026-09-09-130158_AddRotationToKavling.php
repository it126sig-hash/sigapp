<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRotationToKavling extends Migration
{
    public function up()
    {
        $this->forge->addColumn('kavling', [
            'rotation' => [
                'type'       => 'FLOAT',
                'null'       => true,
                'default'    => null,
                'comment'    => 'Sudut rotasi fasad kavling dalam derajat (0-360). NULL = auto-detect via PCA',
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('kavling', 'rotation');
    }
}
