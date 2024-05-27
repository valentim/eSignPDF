<?php

namespace App\Infrastructure\Policies;

use App\Domain\Document\Document;
use App\Domain\User\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class DocumentPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Document $document)
    {
        return $user->id === $document->user_id;
    }
}
