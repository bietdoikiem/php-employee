<?php

namespace App\Models\Services;

use App\Models\Entities\Employee;
use Google\Cloud\Storage\StorageClient;
use App\Utils\ServerLogger;
use App\Utils\BucketConfig;

class EmployeeService {

  public StorageClient $storage;

  public function __construct() {
    $this->storage = new StorageClient([
      'keyFile' => json_decode(file_get_contents(dirname(dirname(__DIR__)) . '\Resources\crafty-coral-281804-c3a7354e1ae6.json'), true),
      'projectId' => 'crafty-coral-281804'
    ]);
    $this->storage->registerStreamWrapper();
  }

  /**
   * Add a new employee to Employees.csv
   * 
   * @param string $first_name
   * ... more info params
   * @return bool true/false
   */
  public function add_employee(string $first_name, string $last_name, string $gender, int $age, string $address, string $phone_number) {
    // Read first to check if match any ID
    $rows_data = $this->read_bucket_csv(BucketConfig::BUCKET_NAME, BucketConfig::EMPLOYEES_FILE, skip_header: false);
    $last_data = end($rows_data);
    $last_id = $last_data[0];
    if ($last_id == "id") {
      $insert_id = 1;
    } else {
      $insert_id = $last_id + 1;
    }
    ServerLogger::log("=> Performing inserting new employee to CSV file!");
    // Insert new data
    $insert_row = array($insert_id, $first_name, $last_name, $gender, $age, $address, $phone_number);
    $result = $this->insert_bucket_csv($insert_row, BucketConfig::BUCKET_NAME, BucketConfig::EMPLOYEES_FILE);
    return $result;
  }

  /**
   * Read all Employees in Employees.csv
   * 
   * @return array $emp_list -> List of employees
   */
  public function read_all_employees(): array {
    $emp_list = array();
    $rows_data = $this->read_bucket_csv(BucketConfig::BUCKET_NAME, BucketConfig::EMPLOYEES_FILE, skip_header: true);
    foreach ($rows_data as &$emp) {
      $emp[0] = intval($emp[0]); // Convert id to int
      $emp[4] = intval($emp[4]); // Convert age to int 
      $emp_obj = new Employee(...$emp);
      array_push($emp_list, $emp_obj);
    }
    return $emp_list;
  }

  /**
   * Edit an Employee in Employees.csv
   * 
   * @return bool true/false -> Edit successful or not
   */
  public function edit_employee(int $id, string $first_name, string $last_name, string $gender, int $age, string $address, string $phone_number): bool {
    $rows_data = $this->read_bucket_csv(BucketConfig::BUCKET_NAME, BucketConfig::EMPLOYEES_FILE, skip_header: false);
    $is_emp_found = false;
    foreach ($rows_data as &$emp) {
      if ($emp[0] == $id) {
        $is_emp_found = true;
        $emp[1] = $first_name;
        $emp[2] = $last_name;
        $emp[3] = $gender;
        $emp[4] = $age;
        $emp[5] = $address;
        $emp[6] = $phone_number;
      }
    }
    $this->write_bucket_csv_multiple($rows_data, BucketConfig::BUCKET_NAME, BucketConfig::EMPLOYEES_FILE);
    return $is_emp_found;
  }

  /**
   * Read a CSV object on Cloud Storage
   * 
   * @param string $bucketName
   * @param string $objectName
   * @return array $rows_data
   */
  public function read_bucket_csv(string $bucketName, string $objectName, bool $skip_header = false): array {
    ServerLogger::log("=> Performing read a CSV file for object" . $objectName);
    $objectURI = "gs://{$bucketName}/{$objectName}";
    $rows_data = $this->read_csv($objectURI, $skip_header);
    ServerLogger::log("=> Successfully read {$objectURI}" . PHP_EOL);
    return $rows_data;
  }

  /**
   * Insert to the end of bucket object file CSV
   * 
   * @param array $row -> Row of data
   * @param string $bucketName
   * @param string $objectName
   * @return bool $result
   */
  public function insert_bucket_csv(array $row, string $bucketName, string $objectName): bool {
    ServerLogger::log("=> Performing insert row to a CSV file for object" . $objectName);
    $objectURI = "gs://{$bucketName}/{$objectName}";
    $result = $this->insert_csv($row, $objectURI);
    if ($result == false) {
      ServerLogger::log("=> CANNOT write {$objectURI}" . PHP_EOL);
      return $result;
    }
    ServerLogger::log("=> Successfully write {$objectURI}" . PHP_EOL);
    return $result;
  }


  /**
   * Write CSV file on bucket storage with multiple rows at a time
   * 
   * @param array $rows -> 2D array of CSV rows
   * @param string $uri -> Link to resource
   * @return bool true/false
   */
  public function write_bucket_csv_multiple(array $rows, string $bucketName, string $objectName): bool {
    ServerLogger::log("=> Performing edit row to a CSV file for object" . $objectName);
    $objectURI = "gs://{$bucketName}/{$objectName}";
    $result = $this->write_csv_multiple($rows, $objectURI);
    if ($result == false) {
      ServerLogger::log("=> CANNOT edit {$objectURI}" . PHP_EOL);
      return $result;
    }
    ServerLogger::log("=> Successfully edit {$objectURI}" . PHP_EOL);
    return true;
  }

  /**
   * Read an object on Cloud Storage
   * @return undefined
   */
  // public function read_bucket_object($bucketName, $objectName) {
  //   ServerLogger::log("=> Performing read for object " . $objectName);
  //   $objectURI = "gs://{$bucketName}/{$objectName}";
  //   ServerLogger::log('=> Successfully read gs://%s//%s' . PHP_EOL, $bucketName, $objectName);
  //   return;
  // }

  /**
   * Read a CSV file
   * 
   * @param string $uri -> URI to source (Ex for Cloud Storage: gs://bucket/object)
   * @return array $row_list -> List of rows
   */
  public function read_csv(string $uri, bool $skip_header = false): array {
    $row_list = array();
    // Open a stream in read-only mode
    if ($stream = fopen($uri, "r")) {
      while (!feof($stream)) {
        for ($i = 0; $row = fgetcsv($stream, separator: ","); ++$i) {
          /* Skip header switch */
          if ($skip_header == False) {
            array_push($row_list, $row);
          } else {
            // print("Skipped")
            $skip_header = False;
            continue;
          }
        }
      }
    }
    fclose($stream);
    return $row_list;
  }



  /**
   * Read a file
   * 
   * @param string $uri -> link to resource
   * @return string $read_str
   */
  public function read_file(string $uri): array {
    // Open a stream in read-only mode
    if ($stream = fopen($uri, "r")) {
      while (!feof($stream)) {
        $read_str = fread($stream, 1024);
      }
    }
    fclose($stream);
    return $read_str;
  }

  /**
   * Insert to the end of CSV file
   * 
   * @param array $row -> Array of CSV data by columns
   * @param string $uri -> Link to resource
   * @return bool true/false
   */
  public function insert_csv(array $row, string $uri): bool {
    if ($stream = fopen($uri, "a")) {
      fputcsv($stream, $row, separator: ",");
    } else {
      return false;
    }
    fclose($stream);
    return true;
  }


  /**
   * Write CSV file with multiple rows at a time
   * 
   * @param array $rows -> 2D array of CSV rows
   * @param string $uri -> Link to resource
   * @return bool true/false
   */
  public function write_csv_multiple(array $rows, string $uri): bool {
    if ($stream = fopen($uri, "w")) {
      foreach ($rows as $line) {
        fputcsv($stream, $line, separator: ",");
      }
    } else {
      return false;
    }
    fclose($stream);
    return true;
  }
}
