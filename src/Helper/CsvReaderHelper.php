<?php

namespace App\Helper;

class CsvReaderHelper
{
    static public function readCsvFile(string $filename): array
    {
        $handle = fopen($filename, 'r');
        $headers = fgetcsv($handle);
        $samples = $labels = [];

        while (($row = fgetcsv($handle)) !== false) {
            $samples[] = $row[0];
            $labels[] = $row[2];
        }

        fclose($handle);
        return [$samples, $labels];
    }

}
