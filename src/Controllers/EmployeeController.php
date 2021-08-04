<?php

namespace App\Controllers;

use App\Utils\View;

class EmployeeController {

  public const ROOT_URL = 'http://localhost:8080/';

  public function __construct() {
  }

  /**
   * Show the index page
   */
  public function index() {
    View::renderTemplate('Employee/index.html');
  }

  /**
   * Add employee
   */
  public function add() {
    header("Location: " . self::ROOT_URL);
  }
}
