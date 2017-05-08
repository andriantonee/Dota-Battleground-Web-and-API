<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;

class BaseController extends Controller
{
    public function getMemberType()
    {
        return 2;
    }
}
