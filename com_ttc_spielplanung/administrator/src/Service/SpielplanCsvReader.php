<?php
namespace Ttc\Component\Spielplanung\Administrator\Service;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/** Parse and validate the entire CSV before any database writes. */
class SpielplanCsvReader
{
    public function read(string $path): array
    {
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw new \RuntimeException(Text::_('COM_TTC_SPIELPLANUNG_SPIELPLAN_IMPORT_ERROR_FILE_READ'));
        }
        try {
            $sample = fread($handle, 4096);
            rewind($handle);
            $encoding = mb_detect_encoding($sample, ['UTF-8', 'Windows-1252', 'ISO-8859-1'], true) ?: 'Windows-1252';
            if (fread($handle, 3) !== "\xEF\xBB\xBF") {
                rewind($handle);
            }
            $convert = static function ($value) use ($encoding) {
                return trim(mb_convert_encoding((string) $value, 'UTF-8', $encoding));
            };
            $header = fgetcsv($handle, 0, ';', '"', '');
            if (!$header) {
                throw new \RuntimeException(Text::_('COM_TTC_SPIELPLANUNG_SPIELPLAN_IMPORT_ERROR_EMPTY_FILE'));
            }
            $header = array_map($convert, $header);
            $required = ['Termin', 'HeimVereinName', 'HeimMannschaftNr', 'GastVereinName',
                'GastMannschaftNr', 'HalleName', 'HalleStrasse', 'HallePLZ', 'HalleOrt'];
            if (array_diff($required, $header) || count($header) !== count(array_unique($header))) {
                throw new \RuntimeException(Text::_('COM_TTC_SPIELPLANUNG_SPIELPLAN_IMPORT_ERROR_MISSING_COLUMNS'));
            }
            $indices = array_flip($header);
            $records = [];
            $line = 1;
            $roman = [1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',7=>'VII',8=>'VIII',9=>'IX',10=>'X',11=>'XI',12=>'XII',13=>'XIII'];
            while (($row = fgetcsv($handle, 0, ';', '"', '')) !== false) {
                $line++;
                $row = array_map($convert, $row);
                if (count(array_filter($row, static function ($value) { return $value !== ''; })) === 0) {
                    continue;
                }
                if (count($row) !== count($header)) {
                    throw new \RuntimeException(Text::sprintf('COM_TTC_SPIELPLANUNG_SPIELPLAN_IMPORT_INVALID_ROW', $line));
                }
                $data = [];
                foreach ($required as $column) {
                    $data[$column] = $row[$indices[$column]];
                }
                $date = \DateTimeImmutable::createFromFormat('!d.m.Y H:i', $data['Termin']);
                $home = filter_var($data['HeimMannschaftNr'], FILTER_VALIDATE_INT);
                $away = filter_var($data['GastMannschaftNr'], FILTER_VALIDATE_INT);
                $venue = $data['HalleName'] . ', ' . $data['HalleStrasse'] . ', ' . $data['HallePLZ'] . ' ' . $data['HalleOrt'];
                if (!$date || $date->format('d.m.Y H:i') !== $data['Termin']
                    || !isset($roman[$home], $roman[$away])
                    || $data['HeimVereinName'] === '' || $data['GastVereinName'] === ''
                    || mb_strlen($data['HeimVereinName']) > 100 || mb_strlen($data['GastVereinName']) > 100
                    || mb_strlen($venue) > 200) {
                    throw new \RuntimeException(Text::sprintf('COM_TTC_SPIELPLANUNG_SPIELPLAN_IMPORT_INVALID_ROW', $line));
                }
                $records[] = (object) [
                    'mannschaft' => $data['HeimVereinName'] === 'TTC Nordend Frankfurt' ? $home : $away,
                    'datum' => $date->format('Y-m-d'), 'uhrzeit' => $date->format('H:i:s'),
                    'heimmannschaft' => $data['HeimVereinName'], 'h_nummer' => $roman[$home],
                    'auswaertsmannschaft' => $data['GastVereinName'], 'a_nummer' => $roman[$away],
                    'ort' => $venue, 'hallennr' => '0', 'ort_key' => 0,
                ];
            }
            if (!$records) {
                throw new \RuntimeException(Text::_('COM_TTC_SPIELPLANUNG_SPIELPLAN_IMPORT_ERROR_NO_DATA'));
            }
            return $records;
        } finally {
            fclose($handle);
        }
    }
}
