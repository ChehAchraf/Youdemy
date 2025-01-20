<?php
namespace App\Interfaces;

interface ICategory {
    public function addCategory(string $name, string $description = null): bool;
    public function updateCategory(int $id, string $name, string $description = null): bool;
    public function deleteCategory(int $id): bool;
    public function getAllCategories(): array;
    public function getCategoryById(int $id): ?object;
} 