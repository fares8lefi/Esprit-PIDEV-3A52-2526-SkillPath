<?php

namespace App\EventSubscriber;

use App\Repository\ModuleRepository;
use CalendarBundle\Entity\Event;
use CalendarBundle\CalendarEvents;
use CalendarBundle\Event\CalendarEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CalendarSubscriber implements EventSubscriberInterface
{
    private ModuleRepository $moduleRepository;
    private UrlGeneratorInterface $router;

    public function __construct(
        ModuleRepository $moduleRepository,
        UrlGeneratorInterface $router
    ) {
        $this->moduleRepository = $moduleRepository;
        $this->router = $router;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CalendarEvents::SET_DATA => 'onCalendarSetData',
        ];
    }

    public function onCalendarSetData(CalendarEvent $calendar)
    {
        $start = $calendar->getStart();
        $end = $calendar->getEnd();
        $filters = $calendar->getFilters();

        // Récupérer les modules programmés dans l'intervalle du calendrier
        $modules = $this->moduleRepository
            ->createQueryBuilder('m')
            ->where('m.scheduledAt BETWEEN :start AND :end')
            ->setParameter('start', $start->format('Y-m-d H:i:s'))
            ->setParameter('end', $end->format('Y-m-d H:i:s'))
            ->getQuery()
            ->getResult();

        foreach ($modules as $module) {
            // Créer un événement pour chaque module
            $moduleEvent = new Event(
                $module->getName(),
                $module->getScheduledAt() // Date de début
            );

            /*
             * Ajouter des options personnalisées (couleur, lien, etc.)
             */
            $moduleEvent->setOptions([
                'backgroundColor' => '#1E88E5',
                'borderColor' => '#1E88E5',
                'textColor' => 'white',
            ]);
            
            $moduleEvent->addOption(
                'url',
                $this->router->generate('admin_module_show', [
                    'id' => $module->getId(),
                ])
            );

            // Ajouter l'événement au calendrier
            $calendar->addEvent($moduleEvent);
        }
    }
}
