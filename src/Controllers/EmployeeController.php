<?php

namespace App\Controllers;

use App\Utils\View;
use App\Models\Services\EmployeeService;
use App\Utils\ServerLogger;

class EmployeeController {

  private $employeeService;

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
  public function add(string $first_name, string $last_name, string $gender, $age, string $address, string $phone_number) {
    ServerLogger::log("Form posted! first_name: " . $first_name);
    $this->employeeService->add_employee($first_name,  $last_name,  $gender,  (int) $age, $address,  $phone_number);
    if (getenv("DEPLOY_URL")) {
      header("Location: " . getenv("DEPLOY_URL"));
    } else {
      header("Location: " . "http://localhost:8080");
    }
  }

  /**
   * Edit an employee
   */
  public function edit(int $id, string $first_name, string $last_name, string $gender, $age, string $address, string $phone_number) {
    ServerLogger::log("Form posted! first_name: " . $first_name);
    $this->employeeService->edit_employee($id, $first_name,  $last_name,  $gender,  (int) $age, $address,  $phone_number);
    if (getenv("DEPLOY_URL")) {
      header("Location: " . getenv("DEPLOY_URL"));
    } else {
      header("Location: " . "http://localhost:8080");
    }
  }
}
