<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EntraineurController extends AbstractController
{
  #[Route('/entraineur', name: 'app_entraineur')]
  public function index(): Response
  {
    $stats = [
      'visitors' => 1294,
      'subscribers' => 1303,
      'sales' => 1345,
      'orders' => 576
  ];

  return $this->render('planning/index.html.twig', [
      'stats' => $stats,
      'page_title' => 'Planning'
  ]);
  }
}
