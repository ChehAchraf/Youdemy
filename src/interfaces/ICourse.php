<?php

namespace App\Interfaces;

interface ICourse {
    public function getAllCourses(): array;
    public function getPendingCourses(): array;
    public function getDeletedCourses(): array;
    public function restoreCourse(int $id): bool;
    public function addCourse(array $courseData): bool;
    public function displayCourse(int $id): ?object;
} 
