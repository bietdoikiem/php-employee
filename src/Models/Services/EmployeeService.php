<?php

namespace App\Models\Services;

use Google\Cloud\Storage\StorageClient;

class EmployeeService {
  public StorageClient $storage;

  public function __construct() {
    $this->storage = new StorageClient([
      'keyFile' => json_decode(file_get_contents(dirname(__DIR__) . '../../crafty-coral-281804-c3a7354e1ae6.json'), true),
      'projectId' => 'crafty-coral-281804'
    ]);
  }
}
