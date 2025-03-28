<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Planning;

final class EntraineurController extends AbstractController
{
  #[Route('/entraineur', name: 'app_entraineur')]
  public function index(EntityManagerInterface $entityManager): Response
{
    $plannings = $entityManager->getRepository(Planning::class)->findAll();
    
    return $this->render('planning/index.html.twig', [
        'plannings' => $plannings,
        'page_title' => 'Liste des plannings'
    ]);
}
}
