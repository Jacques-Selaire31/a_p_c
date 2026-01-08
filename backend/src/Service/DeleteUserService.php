<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

final class DeleteUserService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function delete(User $user): void
    {
        $user->setIsActive(false);
        // $user->setUpdatedAt(new \DateTimeImmutable());

        $this->em->flush();
    }
}
