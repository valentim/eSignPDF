<?php

namespace App\Domain\Document;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Document extends Model
{
    use HasFactory;

    protected $fillable = ['filename', 'signed_filename', 'doc_id', 'signing_page_url', 'user_id', 'uuid', 'signed_at', 'signed_file_upload_at', 'original_file_upload_at'];

    protected static function booted()
    {
        static::creating(function ($document) {
            $document->uuid = (string) Str::uuid();
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }
}
