<?php

use App\Kernel;
use App\Entity\Course;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

require_once dirname(__DIR__) . '/vendor/autoload_runtime.php';

return function (array $context) {
    $kernel = new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);

    return new class($kernel) extends \Symfony\Bundle\FrameworkBundle\Console\Application {
        public function doRun(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output)
        {
            $kernel = $this->getKernel();
            $kernel->boot();
            $container = $kernel->getContainer();
            
            /** @var EntityManagerInterface $em */
            $em = $container->get('doctrine')->getManager();
            $courseRepo = $em->getRepository(Course::class);

            $output->writeln("Checking Courses...");
            $courses = $courseRepo->findAll();
            $count = count($courses);
            $output->writeln("Total Courses found: " . $count);

            if ($count > 0) {
                $firstCourse = $courses[0];
                $output->writeln("First Course: " . $firstCourse->getTitle());
                $output->writeln("Modules count: " . count($firstCourse->getModules()));
            } else {
                $output->writeln("No courses found in database!");
            }

            $output->writeln("\nChecking Filter Logic...");
            $filtered = $courseRepo->findByFilters(null, null, null);
            $output->writeln("Filtered (all null) count: " . count($filtered));

            return 0;
        }
    };
};
