<?php
class DocumentTypes {
    private static $types = [
        1 => 'Kartu Keluarga (KK)',
        2 => 'KTP Suami',
        3 => 'KTP Istri',
        4 => 'Kartu Identitas Anak (KIA)',
        5 => 'Surat Nikah',
        6 => 'Surat Pindah',
        7 => 'Surat Pengantar RT',
        8 => 'Surat Pengantar RW',
        9 => 'Surat Keterangan Kerja',
        10 => 'Surat Pernyataan',
        11 => 'Akta Kelahiran',
        12 => 'Ijazah',
        13 => 'SKCK',
        14 => 'Sertifikat Vaksin',
        15 => 'Dokumen Lainnya'
    ];

    // Get all document types
    public static function getAll() {
        return self::$types;
    }

    // Get single document type name by ID
    public static function getName($id) {
        return self::$types[$id] ?? 'Tidak Diketahui';
    }

    // Generate HTML options for select
    public static function getSelectOptions($selected = '') {
        $html = '<option value="">Pilih Jenis Berkas</option>';
        foreach (self::$types as $id => $name) {
            $isSelected = ($selected == $id) ? 'selected' : '';
            $html .= sprintf(
                '<option value="%d" %s>%s</option>',
                $id,
                $isSelected,
                htmlspecialchars($name)
            );
        }
        return $html;
    }

    // Get document type badge HTML
    public static function getBadge($id) {
        $name = self::getName($id);
        return sprintf(
            '<span class="badge badge-info">%s</span>',
            htmlspecialchars($name)
        );
    }
} 