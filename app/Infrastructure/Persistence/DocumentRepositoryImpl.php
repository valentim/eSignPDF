<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Document\Document;
use App\Domain\Document\DocumentRepository;

class DocumentRepositoryImpl implements DocumentRepository
{
    protected $document;

    public function __construct(Document $document)
    {
        $this->document = $document;
    }

    public function create($data): Document
    {
        return $this->document->create($data);
    }

    public function findById(string $id): ?Document
    {
        return $this->document->find($id);
    }

    public function findByUuid(string $uuid): ?Document
    {
        return $this->document->where('uuid', $uuid)->first();
    }

    public function delete(Document $document): void
    {
        $document->delete();
    }

    public function update(Document $document, $data): Document
    {
        $document->update($data);
        return $document;
    }
}
