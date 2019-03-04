<?php

namespace App\Controller;

use App\Entity\Question;
use App\Form\QuestionType;
use App\Repository\QuestionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/question")
 */
class QuestionController extends AbstractController
{
    /**
     * @Route("/list", name="question_index", methods="GET")
     */
    public function index(QuestionRepository $questionRepository): Response
    {
        return $this->render('question/index.html.twig', ['questions' => $questionRepository->findAll()]);
    }

    /**
     * @Route("/new", name="question_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $question = new Question();
        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $question->setUser($user);
            $question->setCreatedAt(new \Datetime());
            $question->setStatus('active');
            $em = $this->getDoctrine()->getManager();
            $em->persist($question);
            $em->flush();

            return $this->redirectToRoute('faq_index');
        }

        return $this->render('question/new.html.twig', [
            'question' => $question,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="question_show", methods="GET", requirements={"id"="\d+"})
     */
    public function show(Question $question): Response
    {
        return $this->render('question/show.html.twig', ['question' => $question]);
    }

    /**
     * @Route("/{id}/edit", name="question_edit", methods="GET|POST", requirements={"id"="\d+"})
     */
    public function edit(Request $request, Question $question): Response
    {
        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('question_edit', ['id' => $question->getId()]);
        }

        return $this->render('question/edit.html.twig', [
            'question' => $question,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/moderate", name="question_moderate", methods="GET|POST", requirements={"id"="\d+"})
     */
    public function moderate(Request $request, Question $question): Response
    {

        if ($question->getStatus() == 'active') {
            $question->setStatus('inactive');
        } else {
            $question->setStatus('active');
        }

        $this->getDoctrine()->getManager()->flush();
        return $this->redirectToRoute('question_index');
        
    }

    /**
     * @Route("/{id}/remove", name="question_remove", methods="GET|POST", requirements={"id"="\d+"})
     */
    public function remove(Request $request, Question $question): Response
    {

        $em = $this->getDoctrine()->getManager();
        $em->remove($question);
        $em->flush();
        return $this->redirectToRoute('question_index');
        
    }
    
    /**
     * @Route("/{id}", name="question_delete", methods="DELETE", requirements={"id"="\d+"})
     */
    public function delete(Request $request, Question $question): Response
    {
        if ($this->isCsrfTokenValid('delete'.$question->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($question);
            $em->flush();
        }

        return $this->redirectToRoute('question_index');
    }
}
