<?php

namespace App\Controller;


use App\Repository\Task as Repository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;


class ToDo extends AbstractController
{
    /**
     * @Route("/view-all", name="view-all")
     *
     */
    public function all(Repository $repository): Response
    {
        return $this->render('home-page.html.twig', ['task' => $repository->getAll()]);
    }


    /**
     * @Route("/new", name="new")
     *
     */
    public function new(): Response
    {
        return $this->render('add-task.html.twig', ['task' => ['name' => '', 'description' => '']]);
    }


    /**
     * @Route("/create", methods={"POST", "OPTIONS"}, name="create")
     *
     */
    public function create(Repository $repository, Request $request): Response
    {
        $repository->add($request->request->get('name'), $request->request->get('description'));

        return $this->redirect('/view-all');
    }


    /**
     * @Route("/edit/{id}", name="edit")
     *
     */
    public function edit(Repository $repository, int $id): Response
    {
        return $this->render('edit-task.html.twig', ['task' => $repository->getOne($id)]);
    }


    /**
     * @Route("/update/{id}", methods={"POST", "OPTIONS"}, name="update")
     *
     */
    public function update(Repository $repository, Request $request, int $id): Response
    {
        $name = $request->request->get('name');
        $description = $request->request->get('description');

        $repository->update(['name' => $name, 'description' => $description], $id);

        return $this->redirect('/view-all');
    }


    /**
     * @Route("/delete/{id}")
     *
     */
    public function delete(Repository $repository, int $id): Response
    {
        $repository->delete($id);
        return $this->redirect('/view-all');
    }
}