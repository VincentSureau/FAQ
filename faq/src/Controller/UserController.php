<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Repository\RoleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;


/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/list", name="user_index", methods="GET")
     */
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', ['users' => $userRepository->findAll()]);
    }

    /**
     * @Route("/new", name="user_new", methods="GET|POST")
     */
    public function new(Request $request, RoleRepository $repo,  UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->add('password', RepeatedType::class, array(
            'type' => PasswordType::class,
            'invalid_message' => 'The password fields must match.',
            'options' => array('attr' => array('class' => 'password-field')),
            'required' => true,
            'first_options'  => array('label' => 'Password'),
            'second_options' => array('label' => 'Repeat Password'),
        ));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setStatus('active');
            $encodedPassword = $passwordEncoder->encodePassword($user, $user->getPassword());
            $user->setPassword($encodedPassword);
            $user->setRole($repo->findOneByName('user'));
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('user_show', ['id' => $user->getId()]);
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_show", methods="GET")
     */
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', ['user' => $user]);
    }

    /**
     * @Route("/{id}/edit", name="user_edit", methods="GET|POST")
     */
    public function edit(Request $request, User $user, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->add('password', RepeatedType::class, array(
            'type' => PasswordType::class,
            'invalid_message' => 'The password fields must match.',
            'options' => array('attr' => array('class' => 'password-field')),
            'first_options'  => array('label' => 'Password'),
            'second_options' => array('label' => 'Repeat Password'),
        ));
        $form->handleRequest($request);
        $password = $user->getPassword();
        if ($form->isSubmitted() && $form->isValid()) {
            if(empty($request->request->get("user")['password']['first'])) {
                $user->setPassword($password);
            } else {
                $encodedPassword = $passwordEncoder->encodePassword($user, $user->getPassword());
                $user->setPassword($encodedPassword);
            }
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('user_edit', ['id' => $user->getId()]);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/moderate", name="user_moderate", methods="GET|POST", requirements={"id"="\d+"})
     */
    public function moderate(Request $request, User $user): Response
    {

        if ($user->getStatus() == 'active') {
            $user->setStatus('inactive');
        } else {
            $user->setStatus('active');
        }

        $this->getDoctrine()->getManager()->flush();
        return $this->redirectToRoute('user_index');
        
    }

    /**
     * @Route("/{id}/role-moderator", name="user_moderator", methods="GET|POST", requirements={"id"="\d+"})
     */
    public function giveModeratorRole(Request $request, User $user, RoleRepository $roleRepo): Response
    {
        $moderatorRole = $roleRepo->findOneByName('moderateur');
        $user->setRole($moderatorRole);

        $this->getDoctrine()->getManager()->flush();
        return $this->redirectToRoute('user_index');
        
    }

    /**
     * @Route("/{id}/moderate-picture", name="user_moderatepicture", methods="GET|POST", requirements={"id"="\d+"})
     */
    public function moderatePicture(Request $request, User $user): Response
    {
        $user->setPictureUrl('https://via.placeholder.com/250');

        $this->getDoctrine()->getManager()->flush();
        return $this->redirectToRoute('user_index');
        
    }

    /**
     * @Route("/{id}/remove", name="user_remove", methods="GET|POST", requirements={"id"="\d+"})
     */
    public function remove(Request $request, User $user): Response
    {

        $em = $this->getDoctrine()->getManager();
        $em->remove($user);
        $em->flush();
        return $this->redirectToRoute('user_index');
        
    }

    /**
     * @Route("/{id}", name="user_delete", methods="DELETE")
     */
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($user);
            $em->flush();
        }

        return $this->redirectToRoute('user_index');
    }
}
