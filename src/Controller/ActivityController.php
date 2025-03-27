<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Enum\ActivityType;


final class ActivityController extends AbstractController
{
  #[Route('/activity/new', name: 'app_activity_new')]
  public function new(): Response
  {
      return $this->render('activity/new.html.twig', [
          'page_title' => 'Add New Activity',
          'activity_types' => ActivityType::cases()
      ]);
  }
}
