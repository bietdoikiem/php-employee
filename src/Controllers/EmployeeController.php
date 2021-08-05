<?php

namespace App\Controllers;

use App\Utils\View;
use App\Models\Services\EmployeeService;
use App\Utils\ServerLogger;

class EmployeeController {

  private $employeeService;
  public const ROOT_URL = 'http://localhost:8080';

  public function __construct(EmployeeService $employeeService) {
    $this->employeeService = $employeeService;
  }

  /**
   * Show the index page
   */
  public function index() {
    $emp_list = $this->employeeService->read_all_employees();
    View::renderTemplate('Employee/index.html', [
      'emp_list' => $emp_list
    ]);
  }

  /**
   * Add employee
   */
  public function add(string $first_name, string $last_name, string $gender, int $age, string $address, string $phone_number) {
    ServerLogger::log("Form posted! first_name: " . $first_name);
    $this->employeeService->add_employee($first_name,  $last_name,  $gender,  $age, $address,  $phone_number);
    header("Location: " . self::ROOT_URL);
  }
}
