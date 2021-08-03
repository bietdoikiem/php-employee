<?php

namespace Models\Entities;

class Employee {

  public string $id;
  public string $first_name;
  public string $last_name;
  public string $gender;
  public int $age;
  public string $address;
  public string $phone_number;

  public function __construct(string $first_name, string $last_name, string $gender, int $age, string $address, string $phone_number) {
    $this->first_name = $first_name;
    $this->last_name = $last_name;
    $this->gender = $gender;
    $this->age = $age;
    $this->address = $address;
    $this->phone_number = $phone_number;
  }

  /**
   * Get the value of id
   *
   * @return string
   */
  public function getId(): string {
    return $this->id;
  }

  /**
   * Set the value of id
   *
   * @param string $id
   *
   * @return self
   */
  public function setId(string $id): self {
    $this->id = $id;

    return $this;
  }

  /**
   * Get the value of first_name
   *
   * @return string
   */
  public function getFirstName(): string {
    return $this->first_name;
  }

  /**
   * Set the value of first_name
   *
   * @param string $first_name
   *
   * @return self
   */
  public function setFirstName(string $first_name): self {
    $this->first_name = $first_name;

    return $this;
  }

  /**
   * Get the value of last_name
   *
   * @return string
   */
  public function getLastName(): string {
    return $this->last_name;
  }

  /**
   * Set the value of last_name
   *
   * @param string $last_name
   *
   * @return self
   */
  public function setLastName(string $last_name): self {
    $this->last_name = $last_name;

    return $this;
  }

  /**
   * Get the value of gender
   *
   * @return string
   */
  public function getGender(): string {
    return $this->gender;
  }

  /**
   * Set the value of gender
   *
   * @param string $gender
   *
   * @return self
   */
  public function setGender(string $gender): self {
    $this->gender = $gender;

    return $this;
  }

  /**
   * Get the value of age
   *
   * @return int
   */
  public function getAge(): int {
    return $this->age;
  }

  /**
   * Set the value of age
   *
   * @param int $age
   *
   * @return self
   */
  public function setAge(int $age): self {
    $this->age = $age;

    return $this;
  }

  /**
   * Get the value of phone_number
   *
   * @return string
   */
  public function getPhoneNumber(): string {
    return $this->phone_number;
  }

  /**
   * Set the value of phone_number
   *
   * @param string $phone_number
   *
   * @return self
   */
  public function setPhoneNumber(string $phone_number): self {
    $this->phone_number = $phone_number;

    return $this;
  }

  /**
   * Get the value of address
   *
   * @return string
   */
  public function getAddress(): string {
    return $this->address;
  }

  /**
   * Set the value of address
   *
   * @param string $address
   *
   * @return self
   */
  public function setAddress(string $address): self {
    $this->address = $address;

    return $this;
  }
}
