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
  public function add(string $first_name, string $last_name, string $gender, int $age, string $address, string $phone_number) {
    ServerLogger::log("Read object! for first_name" . " {$first_name}");
    $emp_list = $this->read_bucket_csv("php-asm-bucket", "dummy.csv");
    ServerLogger::log($emp_list);
  }

  public function read_all(): array {
    $emp_list = array();
    $rows_data = $this->read_bucket_csv(BucketConfig::BUCKET_NAME, BucketConfig::EMPLOYEES_FILE);
    foreach ($rows_data as $emp) {
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
   * Read an object on Cloud Storage
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
}
