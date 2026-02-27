<?php

namespace App\EventSubscriber;

use CalendarBundle\CalendarEvents;
use CalendarBundle\Event\CalendarEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Repository\ModuleRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CalendarSubscriber implements EventSubscriberInterface
{
    private ModuleRepository $moduleRepository;
    private UrlGeneratorInterface $router;

    public function __construct(ModuleRepository $moduleRepository, UrlGeneratorInterface $router)
    {
        $this->moduleRepository = $moduleRepository;
        $this->router = $router;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CalendarEvents::SET_DATA => 'onCalendarSetData',
        ];
    }

    public function onCalendarSetData(CalendarEvent $calendarEvent): void
    {
        $start = $calendarEvent->getStart();
        $end = $calendarEvent->getEnd();
        $filters = $calendarEvent->getFilters();

        $modules = $this->moduleRepository->createQueryBuilder('m')
            ->where('m.scheduledAt BETWEEN :start AND :end')
            ->setParameter('start', $start->format('Y-m-d H:i:s'))
            ->setParameter('end', $end->format('Y-m-d H:i:s'))
            ->getQuery()
            ->getResult();

        foreach ($modules as $module) {
            $start = $module->getScheduledAt();
            $end = clone $start;
            $end->modify('+1 hour');
            
            $moduleEvent = new \CalendarBundle\Entity\Event(
                $module->getTitle(),
                $start,
                $end
            );

            /*
             * Optional: Add custom options to the event
             */
            $moduleEvent->setOptions([
                'backgroundColor' => '#7C3AED',
                'borderColor' => '#7C3AED',
                'textColor' => 'white',
            ]);
            $moduleEvent->addOption(
                'url',
                $this->router->generate('front_course_show', [
                    'id' => $module->getCourse()->getId(),
                ])
            );

            $calendarEvent->addEvent($moduleEvent);
        }
    }
}
