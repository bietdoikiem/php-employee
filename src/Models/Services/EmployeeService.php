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
    $rows_data = $this->read_bucket_csv(BucketConfig::BUCKET_NAME, BucketConfig::EMPLOYEES_FILE);
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
   * Read all Employees in CSV
   * 
   * @return array $emp_list -> List of employees
   */
  public function read_all_employees(): array {
    $emp_list = array();
    $rows_data = $this->read_bucket_csv(BucketConfig::BUCKET_NAME, BucketConfig::EMPLOYEES_FILE);
    foreach ($rows_data as &$emp) {
      $emp[0] = intval($emp[0]); // Convert id to int
      $emp[4] = intval($emp[4]); // Convert age to int 
      ServerLogger::log("Age: " . $emp[4]);
      $emp_obj = new Employee(...$emp);
      array_push($emp_list, $emp_obj);
    }
    return $emp_list;
  }

  /**
   * Read a CSV object on Cloud Storage
   * 
   * @param string $bucketName
   * @param string $objectName
   * @return array $rows_data
   */
  public function read_bucket_csv(string $bucketName, string $objectName): array {
    ServerLogger::log("=> Performing read a CSV file for object" . $objectName);
    $objectURI = "gs://{$bucketName}/{$objectName}";
    $rows_data = $this->read_csv($objectURI, skip_header: true);
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
   * Read an object on Cloud Storage
   * @return undefined
   */
  public function read_bucket_object($bucketName, $objectName) {
    ServerLogger::log("=> Performing read for object " . $objectName);
    $objectURI = "gs://{$bucketName}/{$objectName}";
    ServerLogger::log('=> Successfully read gs://%s//%s' . PHP_EOL, $bucketName, $objectName);
    return;
  }

  /**
   * Read a CSV object
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
   * Read a file via stream
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
   * Insert to the end of csv file
   * 
   * @param array $row -> array of CSV data by columns
   * @param string $uri -> link to resource
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
}
