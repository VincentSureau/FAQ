<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;


use App\Repository\QuestionRepository;
use App\Repository\TagRepository;
use App\Entity\Question;
use App\Entity\Answer;
use App\Form\AnswerType;

use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;

class FaqController extends Controller // implements \Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface
{
    // public function setPaginator(Knp\Component\Pager\Paginator $paginator)
    // {

    // }

    /**
     * @Route("/", name="faq_index")
     */
    public function index(Request $request, QuestionRepository $repo, TagRepository $tagRepo)
    {
        $paginator = $this->get('knp_paginator');
        $questions = $paginator->paginate(
            $repo->findBy(['status'=> 'active'], ['createdAt' => 'desc']),
            $request->query->getInt('page', 1), 5
        );
        $tags = $tagRepo->findAll();
        return $this->render('faq/index.html.twig', [
            'questions' => $questions,
            'tags' => $tags,
        ]);
    }

    /**
     * @Route("/question/{id}", name="show_question", requirements={"id"="\d+"})
     */
    public function show(Question $id, Request $request)
    {
        $answer = new Answer();
        $form = $this->createForm(AnswerType::class, $answer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $answer->setUser($user);
            $answer->setCreatedAt(new \Datetime());
            $answer->setStatus('active');
            $answer->setQuestion($id);
            $em = $this->getDoctrine()->getManager();
            $em->persist($answer);
            $em->flush();
        }

        return $this->render('faq/show.html.twig', [
            'question' => $id,
            'answer' => $answer,
            'form' => $form->createView(),
        ]);
    }
}
