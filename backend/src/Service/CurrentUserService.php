<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;

class CurrentUserService
{
    public function __construct(
        private Security $security
    ) {}

    public function getUser(): User
    {
        $user = $this->security->getUser();

        return $user;
    }
}
