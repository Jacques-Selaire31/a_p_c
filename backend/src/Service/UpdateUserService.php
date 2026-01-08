<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class UpdateUserService
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $hasher,
        private ValidatorInterface $validator
    ) {}

    public function update(User $user, array $data): User
    {   // 1) Vérification unicité email et pseudo
        if (isset($data['email'])) {
            $existingUser = $this->userRepository->findOneBy(['email' => $data['email']]);
            if ($existingUser && $existingUser->getId() !== $user->getId()) {
                throw new \InvalidArgumentException('Email déjà utilisé');
            }
            $user->setEmail((string) $data['email']);
        }

        if (isset($data['pseudo'])) {
            $existingUser = $this->userRepository->findOneBy(['pseudo' => $data['pseudo']]);
            if ($existingUser && $existingUser->getId() !== $user->getId()) {
                throw new \InvalidArgumentException('Pseudo déjà utilisé');
            }
            $user->setPseudo((string) $data['pseudo']);
        }
        // 2) DOB
        if (isset($data['dob'])) {
            $user->setDob(new \DateTimeImmutable((string) $data['dob']));
        }
        // 3) On valide le password en clair via plainPassword
        if (isset($data['password'])) {
            // 
            $user->setPlainPassword((string) $data['password']);
        }

        // 4) Validation Symfony ValidatorInterface->validate
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            throw new \InvalidArgumentException($errors[0]->getMessage());
        }

        // 5) Hash seulement si password fourni
        if (isset($data['password'])) {
            $user->setPassword($this->hasher->hashPassword($user, $user->getPlainPassword()));
            $user->eraseCredentials();
        }

        $this->em->flush();

        return $user;
    }
}
