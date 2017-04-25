<?php

namespace App\Http\Controllers\Participant;

use App\Http\Controllers\Controller;

class BaseController extends Controller
{
    public function getMemberType()
    {
        return 1;
    }
}
