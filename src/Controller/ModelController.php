<?php

namespace App\Controller;

use App\Entity\Model;
use App\Form\ModelType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/model')]
class ModelController extends AbstractController
{
    #[Route('/', name: 'app_model')]
    public function index(EntityManagerInterface $em): Response
    {
        $models = $em->getRepository(Model::class)->findAll();

        return $this->render('model/index.html.twig', [
            'models' => $models,
        ]);
    }
    #[Route('/edit/{id}', name: 'model_edit')]
    public function edit(Model $model = null, EntityManagerInterface $em, Request $request): Response
    {
        if($model == null) {
            $this->addFlash('danger', 'Model introuvable');
            return $this->redirectToRoute('app_model');
        }

        $form = $this->createForm(ModelType::class, $model);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($model);
            $em->flush();
            $this->addFlash('success', 'Model mis à jour');

            return $this->redirectToRoute('app_model');
        }
        return $this->render('model/show.html.twig', [
            'edit' =>$form->createView(),
            'model' => $model,
        ]);
    }

    #[Route('/new', name: 'model_new')]
    public function new(EntityManagerInterface $em, Request $request): Response
    {
        $model = new Model();
        $form = $this->createForm(ModelType::class, $model);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('success', 'Modèle ajouté');
            $em->persist($model);
            $em->flush();
            return $this->redirectToRoute('app_model');
        }
        return $this->render('model/new.html.twig', [
            'ajout' => $form->createView()
        ]);
    }

    #[Route('/delete/{id}', name: 'model_delete')]
    public function delete(Model $model = null, EntityManagerInterface $em): Response
    {
        if($model == null) {
            $this->addFlash('danger', 'Modèle introuvable');
            return $this->redirectToRoute('app_model');
        }

        $em->remove($model);
        $em->flush();

        $this->addFlash('warning', 'Modèle supprimé');
        return $this->redirectToRoute('app_model');

    }
}
