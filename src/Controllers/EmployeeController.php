<?php

namespace App\Controllers;

use App\Utils\View;

class EmployeeController {

  /**
   * Show the index page
   */
  public function index() {
    View::renderTemplate('Employee/index.html');
  }
}
