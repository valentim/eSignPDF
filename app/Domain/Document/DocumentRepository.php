<?php

namespace App\Domain\Document;

interface DocumentRepository
{
    public function create($data): Document;
    public function findById(string $id): ?Document;
    public function findByUuid(string $uuid): ?Document;
    public function delete(Document $document): void;
    public function update(Document $document, $data): Document;
}
