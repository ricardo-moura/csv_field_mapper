<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Services\ContactService;
use App\Models\CustomAttribute;
use Exception;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    protected $contactService;

    public function __construct(ContactService $service)
    {
        $this->contactService = $service;
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function upload(Request $request)
    {
        try {
            list($csvMappedColumnsData, $csvColumnsHeaders) = $this->contactService->parse($request);
            list($appColumns, $customHeaders) = $this->contactService->getHeaders($csvColumnsHeaders);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }

        $fileName = $request->file('csv_file')->getClientOriginalName();
        $appColumnsAsJson = json_encode($appColumns);
        $customHeadersAsJson = json_encode($customHeaders);
        $csvMappedColumnsDataAsJson = json_encode($csvMappedColumnsData);

        $csvId = $this->contactService->saveCsvData(
            $fileName,
            $appColumnsAsJson,
            $customHeadersAsJson,
            $csvMappedColumnsDataAsJson
        );
        unset($csvMappedColumnsData[0]);

        $csvData = [
            'csv_id' => $csvId,
            'csv_filename' => $fileName,
            'csv_headers' => $appColumns,
            'csv_custom_headers' => $customHeaders,
            'csv_data' => $csvMappedColumnsData
        ];
        return response()->json($csvData, 200);
    }

    public function import(Request $request)
    {
        $csvDataId = $request->input('csv_id');
        $userMappedColumns = $request->except(['csv_id']);

        try {
            list($contactData, $customAttData) = $this->contactService->getDataToImport($csvDataId,$userMappedColumns);

            $contact = new Contact();
            foreach ($contactData as $key => $value) {
                $contact->{$key} = $value;
            }
            $contact->save();
            $contactData = [];

            if (empty($customAttData) === false) {
                foreach ($customAttData as $customAttKey => $customValue) {
                    $customAttr = new CustomAttribute();
                    $customAttr->contact_id = $contact->id;
                    $customAttr->key = $customAttKey;
                    $customAttr->value = $customValue;
                    $customAttr->save();
                }
            }
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }

        return response()->json([
            'message' => 'Import success!!'
        ], 201);
    }
}
