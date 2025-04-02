<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SportifController extends AbstractController
{
  #[Route('/sportif', name: 'home')]
  public function index(): Response
  {
      return $this->render('sportif/index.html.twig');
  }
  
}
