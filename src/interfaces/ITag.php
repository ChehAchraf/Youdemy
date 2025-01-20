<?php
namespace App\Interfaces;

interface ITag {
    public function addTags(array $tags): array;
    public function updateTag(int $id, string $name): bool;
    public function deleteTag(int $id): bool;
    public function getAllTags(): array;
    public function getTagById(int $id): ?object;
    public function getTagsByNames(array $names): array;
} 