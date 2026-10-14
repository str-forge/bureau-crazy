<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private UserRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->repository = $container->get(UserRepository::class);

        $this->entityManager->createQuery('DELETE FROM App\Entity\User')->execute();
    }

    public function testPersistAndFindUserByEmail(): void
    {
        $user = new User('test-repo@ngo.de');
        $user->setPassword('hash123');

        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $savedUser = $this->repository->findOneBy(['email' => 'test-repo@ngo.de']);

        $this->assertNotNull($savedUser);
        $this->assertSame('test-repo@ngo.de', $savedUser->getEmail());
    }

    public function testUpgradePasswordPersistsNewHash(): void
    {
        $user = new User('upgrade@ngo.de');
        $user->setPassword('old_hash');

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->repository->upgradePassword($user, 'new_super_hash');
        $this->entityManager->clear();

        $loadedUser = $this->repository->find($user->getId());
        $this->assertSame('new_super_hash', $loadedUser->getPassword());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }
}
