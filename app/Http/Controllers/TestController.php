<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Auth;
use App\Events\Module\PostRegistrationEvent;
use Event;

/**
 * Class FrontendController
 * @package App\Http\Controllers
 */
class TestController extends Controller
{
    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        Event::fire(new PostRegistrationEvent());
    }
}
