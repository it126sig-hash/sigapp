<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class BackfillMkdtAkadIndentAndAjb extends Migration
{
    public function up()
    {
        if (
            !$this->db->tableExists('mkdt')
            || !$this->db->tableExists('kavling')
            || !$this->db->tableExists('legal')
            || !$this->db->fieldExists('akad_indent_tgl', 'mkdt')
        ) {
            return;
        }

        $this->db->transStart();

        // 1. Kasus Akad Indent (akad_indent = 1):
        // Pindahkan tanggal akad ke akad_indent_tgl (prioritas: legal.ppjb_tanggal, fallback mkdt.akad_tgl),
        // reset akad = 0 dan akad_tgl = NULL, set is_ajb = 'PPJB'.
        $this->db->query("
            UPDATE mkdt m
            LEFT JOIN kavling k ON k.id_kavling = m.id_kavling
            LEFT JOIN legal l ON l.id_legal = k.id_legal
            SET
                m.akad_indent_tgl = COALESCE(
                    m.akad_indent_tgl,
                    CASE WHEN l.ppjb_tanggal > '1970-01-01' THEN l.ppjb_tanggal ELSE NULL END,
                    m.akad_tgl
                ),
                m.akad = 0,
                m.akad_tgl = NULL,
                m.is_ajb = 'PPJB'
            WHERE m.akad_indent = 1
        ");

        // 2. Kasus Akad AJB ((akad_indent = 0 OR akad_indent IS NULL) AND akad = 1):
        // Set akad = 1, is_ajb = 'AJB'.
        // Jika ada riwayat PPJB di legal, isi akad_indent_tgl.
        // Update akad_tgl dengan legal.ajb_tanggal jika tersedia.
        $this->db->query("
            UPDATE mkdt m
            LEFT JOIN kavling k ON k.id_kavling = m.id_kavling
            LEFT JOIN legal l ON l.id_legal = k.id_legal
            SET
                m.akad = 1,
                m.is_ajb = 'AJB',
                m.akad_indent_tgl = COALESCE(
                    m.akad_indent_tgl,
                    CASE WHEN l.ppjb_tanggal > '1970-01-01' THEN l.ppjb_tanggal ELSE NULL END,
                    CASE WHEN m.is_ajb = 'PPJB' AND l.ajb_tanggal > '1970-01-01' THEN m.akad_tgl ELSE NULL END
                ),
                m.akad_tgl = COALESCE(
                    CASE WHEN l.ajb_tanggal > '1970-01-01' THEN l.ajb_tanggal ELSE NULL END,
                    m.akad_tgl
                )
            WHERE (m.akad_indent = 0 OR m.akad_indent IS NULL)
              AND m.akad = 1
        ");

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new \RuntimeException('Gagal melakukan backfill data Akad Indent dan Akad AJB');
        }
    }

    public function down()
    {
        if (
            !$this->db->tableExists('mkdt')
            || !$this->db->fieldExists('akad_indent_tgl', 'mkdt')
        ) {
            return;
        }

        $this->db->transStart();

        // Kembalikan kasus akad_indent = 1 ke status akad = 1 dengan akad_tgl dari akad_indent_tgl
        $this->db->query("
            UPDATE mkdt m
            SET
                m.akad_tgl = COALESCE(m.akad_tgl, m.akad_indent_tgl),
                m.akad = 1,
                m.akad_indent_tgl = NULL
            WHERE m.akad_indent = 1
        ");

        // Reset akad_indent_tgl untuk kasus akad non-indent
        $this->db->query("
            UPDATE mkdt m
            SET
                m.akad_indent_tgl = NULL
            WHERE (m.akad_indent = 0 OR m.akad_indent IS NULL)
              AND m.akad = 1
        ");

        $this->db->transComplete();
    }
}
