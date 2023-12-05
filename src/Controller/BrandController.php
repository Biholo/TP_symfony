<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Form\BrandType;
use App\Entity\Brand;

#[Route('/brand')]
class BrandController extends AbstractController
{
    #[Route('/', name: 'app_brand')]
    public function index(EntityManagerInterface $em): Response
    {
        $brands = $em->getRepository(Brand::class)->findAll();
        return $this->render('brand/index.html.twig', [
            'brands' => $brands,
        ]);
    }

    #[Route('/edit/{id}', name: 'brand_edit')]
    public function edit(Brand $brand = null, EntityManagerInterface $em, Request $request): Response
    {
        if($brand == null) {
            $this->addFlash('danger', 'Marque introuvable');
            return $this->redirectToRoute('app_brand');
        }

        $form = $this->createForm(BrandType::class, $brand);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($brand);
            $em->flush();
            $this->addFlash('success', 'Marque mise à jour');

            return $this->redirectToRoute('app_model');
        }
        return $this->render('brand/show.html.twig', [
            'edit' =>$form->createView(),
            'brand' => $brand,
        ]);
    }

    #[Route('/new', name: 'brand_new')]
    public function new(EntityManagerInterface $em, Request $request): Response
    {
        $brand = new Brand();
        $form = $this->createForm(BrandType::class, $brand);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('success', 'Marque ajouté');

            $imageFile = $form->get('logo')->getData();

            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                try {
                    $imageFile->move(
                        $this->getParameter('upload_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Impossible d\'ajouter l\'image.');
                }
                $brand->setLogo($newFilename);
            }

            $em->persist($brand);
            $em->flush();
            return $this->redirectToRoute('app_brand');
        }

        return $this->render('brand/new.html.twig', [
            'ajout' => $form->createView()
        ]);
    }

    #[Route('/delete/{id}', name: 'brand_delete')]
    public function delete(Brand $brand = null, EntityManagerInterface $em): Response
    {
        if($brand == null) {
            $this->addFlash('danger', 'Marque intoruvable');
            return $this->redirectToRoute('app_brand');
        }

        $em->remove($brand);
        $em->flush();

        $this->addFlash('warning', 'Marque supprimé');
        return $this->redirectToRoute('app_brand');
    }
}
