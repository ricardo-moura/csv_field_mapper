<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CsvData extends Model
{
    protected $fillable = ['csv_filename', 'csv_headers', 'csv_custom_headers', 'csv_data'];
}
