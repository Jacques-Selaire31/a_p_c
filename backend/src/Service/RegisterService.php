<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class RegisterService
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $hasher,
        private ValidatorInterface $validator
    ) {}

    public function createUser(array $data): User
    {
        // 1) Vérification du remplissage des champs
        foreach (['email', 'pseudo', 'dob', 'password'] as $field) {
            if (empty($data[$field])) {
                throw new \InvalidArgumentException("Champ '$field' obligatoire");
            }
        }

        // 2) Vérification Unicité
        if ($this->userRepository->findOneBy(['email' => $data['email']])) {
            throw new \InvalidArgumentException('Email déjà utilisé');
        }
        if ($this->userRepository->findOneBy(['pseudo' => $data['pseudo']])) {
            throw new \InvalidArgumentException('Pseudo déjà utilisé');
        }

        // 3) Hydratation
        $user = new User();
        $user->setEmail((string) $data['email']);
        $user->setPseudo((string) $data['pseudo']);
        $user->setDob(new \DateTimeImmutable((string) $data['dob']));

        // Plain password pour Assert validation Symfony
        $user->setPlainPassword((string) $data['password']);

        // 4) Validation Symfony ValidatorInterface->validate
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            throw new \InvalidArgumentException($errors[0]->getMessage());
        }

        // 5) Hash + persist
        $user->setPassword($this->hasher->hashPassword($user, $user->getPlainPassword()));
        $user->eraseCredentials();

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}
