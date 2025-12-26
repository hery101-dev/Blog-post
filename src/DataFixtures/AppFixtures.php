<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher) {}

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        // Create a demo user
        $user = new User();
        $user->setEmail('demo@example.com');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
        $manager->persist($user);

        // Create 10 fake articles for that user
        for ($i = 0; $i < 10; $i++) {
            $article = new Article();
            $title = ucfirst($faker->words(3, true));
            $article->setTitle($title);
            $article->setSlug($faker->slug());
            $article->setContent($faker->text(180));
            // Use the existing demo image if present
            $article->setImagePath('images/blog_post.jpg');
            $article->setCreatedAt(
                \DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-6 months', 'now'))
            );
            $article->setAuthor($user);

            $manager->persist($article);
        }

        $manager->flush();
    }
}
