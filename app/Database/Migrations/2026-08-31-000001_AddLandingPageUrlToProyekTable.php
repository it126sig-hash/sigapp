<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLandingPageUrlToProyekTable extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('landing_page_url', 'proyek')) {
            $this->forge->addColumn('proyek', [
                'landing_page_url' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'default'    => 'https://sigapp.site',
                    'null'       => false,
                    'after'      => 'logo_pt',
                ],
            ]);
        }

        $this->db->table('proyek')
            ->groupStart()
            ->where('landing_page_url', null)
            ->orWhere('landing_page_url', '')
            ->groupEnd()
            ->update(['landing_page_url' => 'https://sigapp.site']);
    }

    public function down()
    {
        if ($this->db->fieldExists('landing_page_url', 'proyek')) {
            $this->forge->dropColumn('proyek', 'landing_page_url');
        }
    }
}
