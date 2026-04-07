<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $hasher,
    ) {}

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // 1) Catégories
        $categoryNames = ['Formule 1', 'Endurance', 'Rallye', 'MotoGP', 'Histoire', 'GT3', 'WEC', 'Indycar', 'Feeder Series'];
        $categories = [];
        foreach ($categoryNames as $name) {
            $category = new Category();
            $category->setName($name);
            $manager->persist($category);
            $categories[] = $category;
        }

        // 2) Utilisateurs
        $users = [];
        for ($i = 0; $i < 5; $i++) {
            $user = new User();
            $user->setEmail($faker->unique()->safeEmail());
            $user->setPseudo($faker->unique()->userName());
            $user->setDob(new \DateTimeImmutable(
                $faker->dateTimeBetween('-40 years', '-16 years')->format('Y-m-d')
            ));
            $user->setPassword($this->hasher->hashPassword($user, 'Password1!'));
            $manager->persist($user);
            $users[] = $user;
        }

        // 3) Articles
        $articles = [];
        $slugger = new AsciiSlugger();
        for ($i = 0; $i < 10; $i++) {
            $article = new Article();
            $title = $faker->sentence(6);
            $article->setTitle($title);
            $article->setContent([
                'blocks' => [
                    [
                        'type' => 'paragraph',
                        'data' => ['text' => $faker->paragraph(5)]
                    ],
                    [
                        'type' => 'header',
                        'data' => ['text' => $faker->sentence(4), 'level' => 2]
                    ],
                    [
                        'type' => 'paragraph',
                        'data' => ['text' => $faker->paragraph(5)]
                    ],
                    [
                        'type' => 'paragraph',
                        'data' => ['text' => $faker->paragraph(5)]
                    ],
                ]
            ]);
            $firstBlock = $article->getContent()['blocks'][0];
            $text = strip_tags($firstBlock['data']['text']);
            $article->setTextPreview(strlen($text) > 150 ? substr($text, 0, 150) . '...' : $text);
            $article->setAuthor($faker->randomElement($users));
            $slug = $slugger->slug($user->getPseudo() . '-' . $title)->lower()->toString() . '-' . $i;
            $article->setSlug($slug);
            $article->setIsPublished(true);
            $article->setCoverImage('https://picsum.photos/seed/' . ($i + 1) . '/800/400');

            $randomCategories = $faker->randomElements(
                $categories,
                rand(1, min(3, count($categories)))
            );
            foreach ($randomCategories as $category) {
                $article->addCategory($category);
            }

            $manager->persist($article);
            $articles[] = $article;
        }

        // 4) Commentaires avec réponses
        foreach ($articles as $article) {
            $rootComments = [];

            // 3 commentaires racines par article
            for ($i = 0; $i < 3; $i++) {
                $comment = new Comment();
                $comment->setContent($faker->sentence(12));
                $comment->setAuthor($faker->randomElement($users));
                $comment->setArticle($article);
                $manager->persist($comment);
                $rootComments[] = $comment;
            }

            // 2 réponses par commentaire racine
            foreach ($rootComments as $parent) {
                for ($j = 0; $j < 2; $j++) {
                    $reply = new Comment();
                    $reply->setContent(
                        '@' . $parent->getAuthor()->getPseudo() . ' ' . $faker->sentence(8)
                    );
                    $reply->setAuthor($faker->randomElement($users));
                    $reply->setArticle($article);
                    $reply->setParent($parent);
                    $manager->persist($reply);

                    // 1 réponse à une réponse pour tester la récursivité
                    $replyToReply = new Comment();
                    $replyToReply->setContent(
                        '@' . $reply->getAuthor()->getPseudo() . ' ' . $faker->sentence(8)
                    );
                    $replyToReply->setAuthor($faker->randomElement($users));
                    $replyToReply->setArticle($article);
                    $replyToReply->setParent($reply);
                    $manager->persist($replyToReply);
                }
            }
        }

        $manager->flush();
    }
}
