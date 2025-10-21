<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;

/**
 * Application base controller.
 *
 * Extends Laravel's routing controller so child controllers inherit
 * helper methods like middleware(), authorize(), dispatch(), etc.
 */
abstract class Controller extends BaseController
{
    // Intentionally left blank - extends framework base controller
}
