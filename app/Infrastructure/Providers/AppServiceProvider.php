<?php

namespace App\Infrastructure\Providers;

use App\Domain\Document\Document;
use App\Infrastructure\Policies\DocumentPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Domain\Document\DocumentRepository;
use App\Infrastructure\Persistence\DocumentRepositoryImpl;

class AppServiceProvider extends ServiceProvider
{
    protected $policies = [
        Document::class => DocumentPolicy::class,
    ];

    public function register()
    {
        $this->app->bind(DocumentRepository::class, DocumentRepositoryImpl::class);
    }

    public function boot()
    {
        $this->registerPolicies();
    }
}
