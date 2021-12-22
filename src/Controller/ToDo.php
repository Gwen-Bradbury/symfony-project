<?php

namespace App\Controller;

use App\Entity\Task;
use App\Repository\TaskRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;


class ToDo extends AbstractController
{
    /**
     * @Route("/all-tasks", name="all_tasks")
     *
     */
    public function show(TaskRepository $repository): Response
    {
        return $this->render('home-page.html.twig', ['task' => $repository->findAll()]);
    }


    /**
     * @Route("/add-task", name="add_task")
     *
     */
    public function new(Request $request, ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        $task = new Task();

        $form = $this->createFormBuilder($task)
            ->add('name', TextType::class)
            ->add('description', TextType::class)
            ->add('save', SubmitType::class, ['label' => 'Create Task'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $task = $form->getData();

            $entityManager->persist($task);
            $entityManager->flush();
        }
        return $this->renderForm('add-task.html.twig', ['form' => $form]);
    }


    /**
     * @Route("/edit-task/{id}")
     *
     */
    public function update(Request $request, ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $task = $entityManager->getRepository(Task::class)->find($id);

        $form = $this->createFormBuilder($task)
            ->add('name', TextType::class)
            ->add('description', TextType::class)
            ->add('save', SubmitType::class, ['label' => 'Update Task'])
            ->getForm();

        $form->handleRequest($request);

        $entityManager->flush();

        return $this->renderForm('edit-task.html.twig', ['form' => $form]);
    }


    /**
     * @Route("/delete-task/{id}")
     *
     */
    public function delete(ManagerRegistry $doctrine, int $id, TaskRepository $repository): Response
    {
        $entityManager = $doctrine->getManager();
        $task = $entityManager->getRepository(Task::class)->find($id);

        $entityManager->remove($task);
        $entityManager->flush();
        return $this->render('home-page.html.twig', ['task' => $repository->findAll()]);
    }
}