<?php

namespace App\Http\Controllers;

class DownloadsController extends Controller
{
  public function download($file_name) {
    $file_path = public_path('downloads/'.$file_name);
    return response()->download($file_path);
  }
}