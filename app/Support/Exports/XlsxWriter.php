<?php

namespace App\Support\Exports;

use RuntimeException;
use XMLWriter;
use ZipArchive;

/**
 * Penulis file .xlsx sederhana tanpa dependensi tambahan (hanya ext-zip dan ext-xmlwriter).
 * Baris ditulis bertahap ke file sementara sehingga aman untuk data besar.
 *
 * Jika nanti memakai maatwebsite/excel, cukup ganti pemanggilan XlsxWriter::make().
 */
class XlsxWriter
{
    private const STYLE_DEFAULT = 0;

    private const STYLE_HEADER = 1;

    private const STYLE_NUMBER = 2;

    /**
     * Buat file .xlsx dan kembalikan path file sementaranya (hapus setelah dikirim).
     *
     * @param  list<string>  $headers
     * @param  iterable<list<scalar|null>>  $rows
     * @param  list<int>  $numberColumns  Indeks kolom (mulai 0) yang berformat angka ribuan.
     */
    public static function make(string $sheetName, array $headers, iterable $rows, array $numberColumns = []): string
    {
        $sheetFile = tempnam(sys_get_temp_dir(), 'xlsx-sheet');
        $target = tempnam(sys_get_temp_dir(), 'xlsx');

        self::writeSheet($sheetFile, $headers, $rows, $numberColumns);

        $zip = new ZipArchive;

        if ($zip->open($target, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Gagal membuat file xlsx.');
        }

        $zip->addFromString('[Content_Types].xml', self::contentTypes());
        $zip->addFromString('_rels/.rels', self::rootRels());
        $zip->addFromString('xl/workbook.xml', self::workbook($sheetName));
        $zip->addFromString('xl/_rels/workbook.xml.rels', self::workbookRels());
        $zip->addFromString('xl/styles.xml', self::styles());
        $zip->addFile($sheetFile, 'xl/worksheets/sheet1.xml');
        $zip->close();

        @unlink($sheetFile);

        return $target;
    }

    /**
     * @param  list<string>  $headers
     * @param  iterable<list<scalar|null>>  $rows
     * @param  list<int>  $numberColumns
     */
    private static function writeSheet(string $file, array $headers, iterable $rows, array $numberColumns): void
    {
        $xml = new XMLWriter;
        $xml->openUri($file);
        $xml->startDocument('1.0', 'UTF-8', 'yes');
        $xml->startElement('worksheet');
        $xml->writeAttribute('xmlns', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

        $xml->startElement('sheetViews');
        $xml->startElement('sheetView');
        $xml->writeAttribute('workbookViewId', '0');
        $xml->startElement('pane');
        $xml->writeAttribute('ySplit', '1');
        $xml->writeAttribute('topLeftCell', 'A2');
        $xml->writeAttribute('activePane', 'bottomLeft');
        $xml->writeAttribute('state', 'frozen');
        $xml->endElement();
        $xml->endElement();
        $xml->endElement();

        $xml->startElement('cols');
        foreach ($headers as $index => $header) {
            $xml->startElement('col');
            $xml->writeAttribute('min', (string) ($index + 1));
            $xml->writeAttribute('max', (string) ($index + 1));
            $xml->writeAttribute('width', (string) max(12, min(50, mb_strlen($header) + 6)));
            $xml->writeAttribute('customWidth', '1');
            $xml->endElement();
        }
        $xml->endElement();

        $xml->startElement('sheetData');
        self::writeRow($xml, 1, $headers, [], true);

        $rowNumber = 2;
        foreach ($rows as $row) {
            self::writeRow($xml, $rowNumber++, array_values($row), $numberColumns, false);
        }

        $xml->endElement(); // sheetData
        $xml->endElement(); // worksheet
        $xml->endDocument();
        $xml->flush();
    }

    /**
     * @param  list<scalar|null>  $cells
     * @param  list<int>  $numberColumns
     */
    private static function writeRow(XMLWriter $xml, int $rowNumber, array $cells, array $numberColumns, bool $header): void
    {
        $xml->startElement('row');
        $xml->writeAttribute('r', (string) $rowNumber);

        foreach ($cells as $index => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $xml->startElement('c');
            $xml->writeAttribute('r', self::columnName($index).$rowNumber);

            if ($header) {
                $xml->writeAttribute('s', (string) self::STYLE_HEADER);
            }

            if (! $header && is_numeric($value) && ! is_string($value)) {
                $xml->writeAttribute('s', (string) (in_array($index, $numberColumns, true) ? self::STYLE_NUMBER : self::STYLE_DEFAULT));
                $xml->writeElement('v', (string) $value);
            } else {
                $xml->writeAttribute('t', 'inlineStr');
                $xml->startElement('is');
                $xml->startElement('t');
                $xml->writeAttribute('xml:space', 'preserve');
                $xml->text(self::clean((string) $value));
                $xml->endElement();
                $xml->endElement();
            }

            $xml->endElement();
        }

        $xml->endElement();
    }

    /**
     * 0 => A, 25 => Z, 26 => AA.
     */
    private static function columnName(int $index): string
    {
        $name = '';

        for ($index++; $index > 0; $index = intdiv($index - 1, 26)) {
            $name = chr(65 + ($index - 1) % 26).$name;
        }

        return $name;
    }

    /**
     * Buang karakter kontrol yang tidak valid di XML 1.0.
     */
    private static function clean(string $value): string
    {
        return preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', $value) ?? '';
    }

    private static function contentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            .'</Types>';
    }

    private static function rootRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>';
    }

    private static function workbook(string $sheetName): string
    {
        $name = htmlspecialchars(mb_substr(preg_replace('/[\\\\\/\?\*\[\]:]/', ' ', $sheetName) ?? 'Sheet1', 0, 31), ENT_XML1 | ENT_QUOTES, 'UTF-8');

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets><sheet name="'.$name.'" sheetId="1" r:id="rId1"/></sheets></workbook>';
    }

    private static function workbookRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            .'</Relationships>';
    }

    private static function styles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<numFmts count="1"><numFmt numFmtId="164" formatCode="#,##0"/></numFmts>'
            .'<fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font></fonts>'
            .'<fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FFE5E7EB"/><bgColor indexed="64"/></patternFill></fill></fills>'
            .'<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="3">'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            .'<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/>'
            .'<xf numFmtId="164" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>'
            .'</cellXfs>'
            .'<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            .'</styleSheet>';
    }
}
