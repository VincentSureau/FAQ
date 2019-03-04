<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Form\AnswerType;
use App\Repository\AnswerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/answer")
 */
class AnswerController extends AbstractController
{
    /**
     * @Route("/list", name="answer_index", methods="GET")
     */
    public function index(AnswerRepository $answerRepository): Response
    {
        return $this->render('answer/index.html.twig', ['answers' => $answerRepository->findAll()]);
    }

    /**
     * @Route("/new", name="answer_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $answer = new Answer();
        $form = $this->createForm(AnswerType::class, $answer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $answer->setUser($user);
            $answer->setCreatedAt(new \Datetime());
            $answer->setStatus('active');
            $em = $this->getDoctrine()->getManager();
            $em->persist($answer);
            $em->flush();

            return $this->redirectToRoute('answer_index');
        }

        return $this->render('answer/new.html.twig', [
            'answer' => $answer,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="answer_show", methods="GET")
     */
    public function show(Answer $answer): Response
    {
        return $this->render('answer/show.html.twig', ['answer' => $answer]);
    }

    /**
     * @Route("/{id}/edit", name="answer_edit", methods="GET|POST")
     */
    public function edit(Request $request, Answer $answer): Response
    {
        $form = $this->createForm(AnswerType::class, $answer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('answer_edit', ['id' => $answer->getId()]);
        }

        return $this->render('answer/edit.html.twig', [
            'answer' => $answer,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/moderate", name="answer_moderate", methods="GET|POST", requirements={"id"="\d+"})
     */
    public function moderate(Request $request, Answer $answer): Response
    {

        if ($answer->getStatus() == 'active') {
            $answer->setStatus('inactive');
        } else {
            $answer->setStatus('active');
        }

        $this->getDoctrine()->getManager()->flush();
        return $this->redirectToRoute('answer_index');
        
    }

    /**
     * @Route("/{id}/remove", name="answer_remove", methods="GET|POST", requirements={"id"="\d+"})
     */
    public function remove(Request $request, Answer $answer): Response
    {

        $em = $this->getDoctrine()->getManager();
        $em->remove($answer);
        $em->flush();
        return $this->redirectToRoute('answer_index');
        
    }

    /**
     * @Route("/{id}", name="answer_delete", methods="DELETE")
     */
    public function delete(Request $request, Answer $answer): Response
    {
        if ($this->isCsrfTokenValid('delete'.$answer->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($answer);
            $em->flush();
        }

        return $this->redirectToRoute('answer_index');
    }
}
