<?php

namespace App\Services;

use App\Models\CsvData;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class ContactService
{
    public function parse(Request $request)
    {
        $path = $request->file('csv_file')->getRealPath();

        $originalCsvFileAsArray = array_map('str_getcsv', file($path));

        if (array_key_exists(0, $originalCsvFileAsArray) === false ||
            array_key_exists(1, $originalCsvFileAsArray) === false) {
            throw new Exception('Error while trying to parse the file!');
        }

        $csvColumnsHeaders = $originalCsvFileAsArray[0];

        $csvColumnsData = [];
        $csvColumnsData[] = $originalCsvFileAsArray[1];

        $csvMappedColumnsData = array_map(function ($csvColumnData) use ($csvColumnsHeaders) {
            if (count($csvColumnsHeaders) !== count($csvColumnData)) {
                throw new Exception('The .csv file has different amounts of data and columns.');
            }
            return array_combine($csvColumnsHeaders, $csvColumnData);
        }, $csvColumnsData);

        if (empty($csvMappedColumnsData) === true) {
            throw new Exception('The pre mapping could not be empty!');
        }

        return [$csvMappedColumnsData, $csvColumnsHeaders];
    }

    public function getHeaders($csvColumnsHeaders)
    {
        $appColumns = Config::get('constants.app_columns');

        $customHeaders = [];

        foreach ($csvColumnsHeaders as $columnHeader) {
            if (in_array($columnHeader, $appColumns) === false) {
                $customHeaders[] = $columnHeader;
            }
        }

        return [$appColumns, $customHeaders];
    }

    public function saveCsvData(
        $fileName,
        $appColumnsAsJson,
        $customHeadersAsJson,
        $csvMappedColumnsData
    )
    {
        $data = [
            'csv_filename' => $fileName,
            'csv_headers' => $appColumnsAsJson,
            'csv_custom_headers' => $customHeadersAsJson,
            'csv_data' => $csvMappedColumnsData,
        ];

        $csv = CsvData::create($data);

        return $csv->id;
    }

    public function getDataToImport(
        $csvDataId,
        $userMappedColumns
    )
    {
        $csvDataFromDB = CsvData::find($csvDataId);
        $csvData = collect(json_decode($csvDataFromDB->csv_data));

        $contactData = [];
        $customAttData = [];

        $appColumns = Config::get('constants.app_columns');
        $requiredColumns = Config::get('constants.required_app_columns');

        foreach ($csvData as $rowNumber => $row) {
            foreach ($row as $columnName => $columnData) {
                foreach ($appColumns as $appColumn) {
                    if ($columnName === $appColumn && empty($columnData) === false) {
                        $contactData[$columnName] = $columnData;
                    }
                }
            }
            foreach ($userMappedColumns as $originalColumnName => $userMappedColumn) {
                if (in_array($userMappedColumn, $appColumns) === true) {
                    $contactData[$userMappedColumn] = $row->$originalColumnName;
                } else {
                    $customAttData[$userMappedColumn] = $row->$originalColumnName;
                }
            }
        }

        foreach ($appColumns as $appColumn) {
            if (array_key_exists($appColumn, $contactData) === false) {
                if (in_array($appColumn, $requiredColumns) === true) {
                    $errosMsg = sprintf("You don't have mapped a required field. Field: %s", $appColumn);
                    throw new Exception($errosMsg);
                }
                $contactData[$appColumn] = null;
            }
        }

        return [$contactData, $customAttData];
    }
}
