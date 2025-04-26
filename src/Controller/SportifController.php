<?php

namespace App\Controller;

use App\Entity\Salle;
use App\Enum\ActivityType;
use App\Entity\Abonnement;
use App\Entity\EquipeEvent;
use App\Entity\User;
use App\Entity\Cours;
use App\Entity\Paiement;
use App\Enum\ObjectifCours;
use App\Form\SportifParticipationType;
use App\Repository\AbonnementRepository;
use App\Repository\EquipeEventRepository;
use App\Repository\SalleRepository;
use App\Repository\CoursRepository;
use App\Service\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Psr\Log\LoggerInterface;

final class SportifController extends AbstractController
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    #[Route('/sportif', name: 'home')]
    public function sportif(SalleRepository $salleRepository): Response
    {
        $salles = $salleRepository->findAll();
        return $this->render('sportif/index.html.twig', [
            'salles' => $salles,
        ]);
    }

    #[Route('/sportif/planning', name: 'plan')]
    public function index(CoursRepository $coursRepository): Response
    {
        $cours = $coursRepository->findAll();
        $events = [];

        foreach ($cours as $cour) {
            $events[] = [
                'title' => $cour->getTitle(),
                'start' => $this->mergeDateTime($cour->getDateDebut(), $cour->getHeurDebut()),
                'end' => $this->mergeDateTime($cour->getDateDebut(), $cour->getHeurFin()),
                'color' => $this->getColorForObjectif($cour->getObjectif()),
                'description' => $cour->getDescription(),
                'extendedProps' => [
                    'activite' => $cour->getActivité() ? $cour->getActivité()->getNom() : null,
                    'salle' => $cour->getSalle() ? $cour->getSalle()->getNom() : null,
                ],
            ];
        }

        return $this->render('sportif/planning.html.twig', [
            'events' => $events,
        ]);
    }

    #[Route('/sportif/salle/{id}', name: 'sportif_salle_details')]
    public function salleDetails(
        Salle $salle,
        AbonnementRepository $abonnementRepository,
        EquipeEventRepository $equipeEventRepository
    ): Response {
        $abonnements = $abonnementRepository->findBy(['salle' => $salle]);
        $equipeEvents = $equipeEventRepository->findBySalle($salle);
        $sportif = $this->getUser();

        if (!$sportif) {
            throw $this->createAccessDeniedException('Vous devez être connecté en tant que sportif.');
        }

        return $this->render('sportif/salle_details.html.twig', [
            'salle' => $salle,
            'abonnements' => $abonnements,
            'equipe_events' => $equipeEvents,
            'sportif' => $sportif,
            'stripe_public_key' => $this->getParameter('stripe_public_key')
        ]);
    }

    #[Route('/sportif/salle/{id}/events', name: 'sportif_salle_events', methods: ['GET'])]
    public function salleEvents(Salle $salle, EquipeEventRepository $equipeEventRepository): JsonResponse
    {
        $equipeEvents = $equipeEventRepository->findBySalle($salle);
        $eventsData = [];

        foreach ($equipeEvents as $equipeEvent) {
            $event = $equipeEvent->getEvent();
            $equipe = $equipeEvent->getEquipe();
            $eventsData[] = [
                'id' => $equipeEvent->getId(),
                'nom' => $event->getNom(),
                'imageUrl' => $event->getImageUrl(),
                'type' => $event->getType() ? $event->getType()->value : 'N/A',
                'dateDay' => $event->getDate()->format('d'),
                'dateMonth' => $event->getDate()->format('M'),
                'heureDebut' => $event->getHeureDebut()->format('H:i'),
                'heureFin' => $event->getHeureFin()->format('H:i'),
                'reward' => $event->getReward() ? $event->getReward()->value : 'N/A',
                'description' => $event->getDescription(),
                'lieu' => $event->getLieu(),
                'equipeNom' => $equipe->getNom(),
                'detailsUrl' => $this->generateUrl('app_equipe_event_show', ['id' => $equipeEvent->getId()]),
                'joinUrl' => $this->generateUrl('app_equipe_event_join', ['id' => $equipeEvent->getId()]),
            ];
        }

        return new JsonResponse(['equipe_events' => $eventsData]);
    }

    #[Route('/sportif/equipe-event/join/{id}', name: 'app_equipe_event_join', methods: ['GET', 'POST'])]
    public function joinEquipeEvent(
        Request $request,
        EquipeEvent $equipeEvent,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var User $sportif */
        $sportif = $this->getUser();

        if (!$sportif || !in_array('ROLE_SPORTIF', $sportif->getRoles(), true)) {
            $this->logger->debug('User not logged in or not a sportif');
            $this->addFlash('error', 'Vous devez être connecté en tant que sportif pour participer.');
            return $this->redirectToRoute('sportif_salle_details', ['id' => $equipeEvent->getEvent()->getSalle()->getId()]);
        }

        $existingParticipation = $entityManager->getRepository(User::class)
            ->createQueryBuilder('u')
            ->where('u.id = :sportifId')
            ->andWhere('u.equipe = :equipeId')
            ->setParameter('sportifId', $sportif->getId())
            ->setParameter('equipeId', $equipeEvent->getEquipe()->getId())
            ->getQuery()
            ->getOneOrNullResult();

        if ($existingParticipation) {
            $this->logger->debug('Sportif already in team', ['sportif_id' => $sportif->getId(), 'equipe_id' => $equipeEvent->getEquipe()->getId()]);
            $this->addFlash('error', 'Vous participez déjà à cette équipe pour cet événement.');
            return $this->redirectToRoute('sportif_salle_details', ['id' => $equipeEvent->getEvent()->getSalle()->getId()]);
        }

        $equipe = $equipeEvent->getEquipe();
        if ($equipe->getNombreMembres() >= 8) {
            $this->logger->debug('Team is full', ['equipe_id' => $equipe->getId()]);
            $this->addFlash('error', 'Cette équipe est complète (8/8).');
            return $this->redirectToRoute('sportif_salle_details', ['id' => $equipeEvent->getEvent()->getSalle()->getId()]);
        }

        $form = $this->createForm(SportifParticipationType::class, $sportif);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sportif->setEquipe($equipe);
            $equipe->setNombreMembres($equipe->getNombreMembres() + 1);

            $entityManager->persist($sportif);
            $entityManager->persist($equipe);
            $entityManager->flush();

            $this->logger->info('Sportif joined team event', [
                'sportif_id' => $sportif->getId(),
                'equipe_id' => $equipe->getId(),
                'event_id' => $equipeEvent->getEvent()->getId()
            ]);
            $this->addFlash('success', sprintf('Le sportif %s %s a été ajouté à l\'équipe %s pour cet événement !', $sportif->getPrenom(), $sportif->getNom(), $equipe->getNom()));
            return $this->redirectToRoute('sportif_salle_details', ['id' => $equipeEvent->getEvent()->getSalle()->getId()]);
        }

        return $this->render('sportif/join_equipe_event.html.twig', [
            'form' => $form->createView(),
            'equipe_event' => $equipeEvent,
        ]);
    }

    #[Route('/sportif/equipe-event/show/{id}', name: 'app_equipe_event_show', methods: ['GET'])]
    public function showEquipeEvent(EquipeEvent $equipeEvent): Response
    {
        return $this->render('sportif/event_details.html.twig', [
            'equipe_event' => $equipeEvent,
        ]);
    }

    #[Route('/sportif/cours', name: 'cours_sportif')]
    public function cours(CoursRepository $repo): Response
    {
        $cours = $repo->findAll();
        return $this->render('sportif/cours.html.twig', ['cours' => $cours]);
    }

    #[Route('/sportif/mes-abonnements', name: 'sportif_abonnements')]
    public function mesAbonnements(EntityManagerInterface $em): Response
    {
        $sportif = $this->getUser();
        $paiements = $em->getRepository(Paiement::class)->findBy(['user' => $sportif, 'status' => 'succeeded']);
        
        return $this->render('sportif/mes_abonnements.html.twig', [
            'paiements' => $paiements,
        ]);
    }

    #[Route('/subscribe/{id}/pay', name: 'sportif_subscribe_pay', methods: ['GET'])]
#[IsGranted('ROLE_SPORTIF')]
public function pay(Abonnement $abonnement, StripeService $stripeService, EntityManagerInterface $em): JsonResponse
{
    $this->logger->info('Pay route accessed', [
        'abonnement_id' => $abonnement->getId(),
        'user' => $this->getUser() ? $this->getUser()->getId() : 'anonymous'
    ]);

    $sportif = $this->getUser();
    if (!$sportif) {
        $this->logger->error('User not authenticated', ['abonnement_id' => $abonnement->getId()]);
        return new JsonResponse(['error' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
    }

    // Verify user exists in database
    if (!$em->getRepository(User::class)->find($sportif->getId())) {
        $this->logger->error('User not found in database', ['user_id' => $sportif->getId()]);
        return new JsonResponse(['error' => 'Utilisateur introuvable dans la base de données'], Response::HTTP_BAD_REQUEST);
    }

    $amount = $abonnement->getTarif();
    if ($amount <= 0) {
        $this->logger->error('Invalid amount', ['amount' => $amount, 'abonnement_id' => $abonnement->getId()]);
        return new JsonResponse(['error' => 'Montant invalide'], Response::HTTP_BAD_REQUEST);
    }

    try {
        $this->logger->info('Creating PaymentIntent', ['amount' => $amount * 100, 'currency' => 'tnd']);
        $paymentIntentId = $stripeService->createPaymentIntent($amount * 100, 'usd');
        $this->logger->info('PaymentIntent created', ['paymentIntentId' => $paymentIntentId]);

        $paiement = new Paiement();
        $paiement->setUser($sportif);
        $paiement->setAbonnement($abonnement);
        $paiement->setAmount($amount);
        $paiement->setCurrency('usd');
        $paiement->setStatus('pending');
        $paiement->setPaymentIntentId($paymentIntentId);
        $paiement->setCreatedAt(new \DateTimeImmutable());
        $paiement->setUpdatedAt(new \DateTimeImmutable());
        $paiement->setDateDebut(new \DateTimeImmutable());
        $dateFin = match ($abonnement->getType()->value) {
            'mois' => $paiement->getDateDebut()->modify('+1 month'),
            'trimestre' => $paiement->getDateDebut()->modify('+3 months'),
            'année' => $paiement->getDateDebut()->modify('+1 year'),
            default => $paiement->getDateDebut()->modify('+1 month'),
        };
        $paiement->setDateFin($dateFin);

        $this->logger->info('Persisting Paiement entity', ['abonnement_id' => $abonnement->getId(), 'user_id' => $sportif->getId()]);
        $em->persist($paiement);
        $em->flush();

        $this->logger->info('Payment entity saved', ['paiement_id' => $paiement->getId()]);

        $responseData = [
            'paymentIntentId' => $paymentIntentId,
            'abonnement' => [
                'id' => $abonnement->getId(),
                'tarif' => $abonnement->getTarif(),
                'type' => $abonnement->getType()->value,
                'activite' => $abonnement->getActivite() ? $abonnement->getActivite()->getNom() : null,
            ],
        ];

        $this->logger->info('Pay response sent', ['response' => $responseData]);

        return new JsonResponse($responseData);
    } catch (\Exception $e) {
        $this->logger->error('Payment creation failed', [
            'error' => $e->getMessage(),
            'abonnement_id' => $abonnement->getId(),
            'stack_trace' => $e->getTraceAsString()
        ]);
        return new JsonResponse(['error' => 'Erreur lors de la création du paiement : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
    #[Route('/subscribe/confirm', name: 'sportif_subscribe_confirm', methods: ['POST'])]
    public function confirmSubscription(Request $request, EntityManagerInterface $entityManager, StripeService $stripeService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $this->logger->info('ConfirmSubscription data received', ['data' => $data]);

        // Vérification des paramètres avec log détaillé
        $missingParams = [];
        if (empty($data['paymentIntentId'])) $missingParams[] = 'paymentIntentId';
        if (empty($data['paymentMethodId'])) $missingParams[] = 'paymentMethodId';
        if (empty($data['abonnementId'])) $missingParams[] = 'abonnementId';

        if (!empty($missingParams)) {
            $this->logger->error('Missing parameters in confirmSubscription', [
                'missing' => $missingParams,
                'data' => $data
            ]);
            return new JsonResponse(['error' => 'Paramètres manquants : ' . implode(', ', $missingParams)], Response::HTTP_BAD_REQUEST);
        }

        $paymentIntentId = $data['paymentIntentId'];
        $paymentMethodId = $data['paymentMethodId'];
        $abonnementId = $data['abonnementId'];

        // Vérifier l'abonnement
        $abonnement = $entityManager->getRepository(Abonnement::class)->find($abonnementId);
        if (!$abonnement) {
            $this->logger->error('Abonnement not found', ['abonnement_id' => $abonnementId]);
            return new JsonResponse(['error' => 'Abonnement introuvable'], Response::HTTP_NOT_FOUND);
        }

        // Vérifier l'utilisateur
        $sportif = $this->getUser();
        if (!$sportif) {
            $this->logger->error('User not authenticated', ['abonnement_id' => $abonnementId]);
            return new JsonResponse(['error' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            // Confirmer le PaymentIntent avec Stripe
            $paymentIntent = $stripeService->confirmPaymentIntent($paymentIntentId, $paymentMethodId);
            $status = $paymentIntent->status;

            if ($status === 'succeeded') {
                // Mettre à jour l'entité Paiement
                $paiement = $entityManager->getRepository(Paiement::class)->findOneBy(['paymentIntentId' => $paymentIntentId]);
                if (!$paiement) {
                    $this->logger->error('Paiement not found for PaymentIntent', ['paymentIntentId' => $paymentIntentId]);
                    return new JsonResponse(['error' => 'Paiement introuvable'], Response::HTTP_NOT_FOUND);
                }

                $paiement->setStatus('succeeded');
                $entityManager->persist($paiement);
                $entityManager->flush();

                $this->logger->info('Subscription confirmed and Paiement updated', [
                    'sportif_id' => $sportif->getId(),
                    'abonnement_id' => $abonnementId,
                    'paiement_id' => $paiement->getId()
                ]);

                return new JsonResponse([
                    'status' => 'succeeded',
                    'dateDebut' => $paiement->getDateDebut()->format('c'),
                    'dateFin' => $paiement->getDateFin()->format('c')
                ]);
            } else {
                $this->logger->error('Payment failed', [
                    'paymentIntentId' => $paymentIntentId,
                    'status' => $status
                ]);
                return new JsonResponse(['error' => 'Paiement échoué : statut ' . $status], Response::HTTP_PAYMENT_REQUIRED);
            }
        } catch (\Exception $e) {
            $this->logger->error('Error confirming payment', [
                'error' => $e->getMessage(),
                'paymentIntentId' => $paymentIntentId,
                'abonnement_id' => $abonnementId,
                'stack_trace' => $e->getTraceAsString()
            ]);
            return new JsonResponse(['error' => 'Erreur lors de la confirmation du paiement : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Merge a DateTime date with a DateTime time into a full ISO string.
     *
     * @param \DateTime $date
     * @param \DateTime $time
     * @return string
     */
    private function mergeDateTime(\DateTime $date, \DateTime $time): string
    {
        $dateStr = $date->format('Y-m-d');
        $timeStr = $time->format('H:i:s');
        return $dateStr . 'T' . $timeStr;
    }

    /**
     * Get color based on the course objective.
     *
     * @param ObjectifCours|null $objectif
     * @return string
     */
    private function getColorForObjectif(?ObjectifCours $objectif): string
    {
        if (null === $objectif) {
            return '#CCCCCC';
        }

        return match ($objectif) {
            ObjectifCours::PERTE_POIDS => '#FF5733',
            ObjectifCours::PRISE_DE_MASSE => '#33FF57',
            ObjectifCours::ENDURANCE => '#3357FF',
            ObjectifCours::RELAXATION => '#F033FF',
            default => '#CCCCCC',
        };
    }
}