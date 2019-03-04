<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\Question;
use App\Entity\User;
use App\Entity\Tag;
use App\Entity\Answer;
use App\Entity\Role;
use Faker;

class FaqFixtures extends Fixture
{

    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
         $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $roleAdmin = new Role;
        $roleAdmin->setName('admin');
        $roleAdmin->setCode('ROLE_ADMIN');
        $manager->persist($roleAdmin);

        $roleModerateur = new Role;
        $roleModerateur->setName('moderateur');
        $roleModerateur->setCode('ROLE_MODERATEUR');
        $manager->persist($roleModerateur);

        $roleUser = new Role;
        $roleUser->setName('user');
        $roleUser->setCode('ROLE_USER');
        $manager->persist($roleUser);

        $admin = new User;
        $admin->setUsername('admin');
        $admin->setPassword($this->passwordEncoder->encodePassword(
        $admin,
        'admin'
        ));
        $admin->setEmail('admin@admin.fr');
        $admin->setPictureUrl('https://api.adorable.io/avatars/285/'. $admin->getEmail() .'.png');
        $admin->setRole($roleAdmin);
        $admin->setStatus('active');
        $manager->persist($admin);

        $user1 = new User;
        $user1->setUsername('user');
        $user1->setPassword($this->passwordEncoder->encodePassword(
        $user1,
        'user'
        ));
        $user1->setEmail('user@user.fr');
        $user1->setPictureUrl('https://api.adorable.io/avatars/285/'. $user1->getEmail() .'.png');
        $user1->setRole($roleUser);
        $user1->setStatus('active');
        $manager->persist($user1);

        $moderateur = new User;
        $moderateur->setUsername('moderateur');
        $moderateur->setPassword($this->passwordEncoder->encodePassword(
        $moderateur,
        'moderateur'
        ));
        $moderateur->setEmail('moderateur@moderateur.fr');
        $moderateur->setPictureUrl('https://api.adorable.io/avatars/285/'. $moderateur->getEmail() .'.png');
        $moderateur->setRole($roleModerateur);
        $moderateur->setStatus('active');
        $manager->persist($moderateur);
        

        
        $faker = Faker\Factory::create();
        // $product = new Product();
        // $manager->persist($product);

        for($i = 0; $i <= 20; $i++) {
            $user = new User;
            $user->setUsername($faker->unique()->firstName());
            $user->setEmail($faker->unique()->email());
            $user->setPassword('$argon2i$v=19$m=1024,t=2,p=2$UXFxdUtzNjFnaWViYlFuTw$foYvu9OFAVG/EI7VIKM+s/UBKQGzAN485kt64uic6Xw
            ');
            $user->setPassword($this->passwordEncoder->encodePassword(
            $user,
            'user'
            ));
            $user->setPictureUrl('https://api.adorable.io/avatars/285/'. $user->getEmail() .'.png');
            $user->setRole($roleUser);
            $user->setStatus('active');
            $manager->persist($user);

            $tag = new Tag;
            $tag->setName($faker->word);
            $manager->persist($tag);

            for($j = 0; $j < mt_rand(0, 3); $j++) {
                $question = new Question;
                $question->setContent($faker->paragraph($nbSentences = 3, $variableNbSentences = true));
                $question->setStatus('active');
                $question->setCreatedAt($faker->dateTimeBetween($startDate = '-2 years', $endDate = 'now', $timezone = null));
                $question->setUser($user);
                $question->addTag($tag);
                $manager->persist($question);

                for($k = 0; $k < mt_rand(1, 10); $k++) {
                    $answeringUser = new User;
                    $answeringUser->setUsername($faker->unique()->firstName());
                    $answeringUser->setEmail($faker->unique()->email());
                    $answeringUser->setPassword($this->passwordEncoder->encodePassword(
                    $answeringUser,
                    'user'
                    ));
                    $answeringUser->setStatus('active');
                    $answeringUser->setRole($roleUser);
                    $answeringUser->setPictureUrl('https://api.adorable.io/avatars/285/'. $answeringUser->getEmail() .'.png');
                    $manager->persist($answeringUser);

                    $answer = new Answer;
                    $answer->setContent($faker->sentence($nbWords = 20, $variableNbWords = true));
                    $answer->setStatus('active');
                    $answer->setCreatedAt($faker->dateTimeBetween($startDate = '-2 years', $endDate = 'now', $timezone = null));
                    $answer->setUser($answeringUser);
                    $answer->setQuestion($question);
                    $manager->persist($answer);
                    if($k == 0) {
                        $question->setBestAnswer($answer);
                        $manager->persist($question);
                    }
                    

                }
            }
        }


        $manager->flush();
    }
}
