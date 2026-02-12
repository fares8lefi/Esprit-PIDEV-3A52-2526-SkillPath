<?php

use App\Kernel;
use App\Entity\Course;
use Symfony\Component\Dotenv\Dotenv;
use Doctrine\ORM\EntityManagerInterface;

require __DIR__ . '/vendor/autoload.php';

(new Dotenv())->bootEnv(__DIR__.'/.env');

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();
$container = $kernel->getContainer();

/** @var EntityManagerInterface $em */
$em = $container->get('doctrine')->getManager();
$courseRepo = $em->getRepository(Course::class);

echo "Checking Courses...\n";
$courses = $courseRepo->findAll();
$count = count($courses);
echo "Total Courses found: " . $count . "\n";

if ($count > 0) {
    $firstCourse = $courses[0];
    echo "First Course: " . $firstCourse->getTitle() . "\n";
    echo "Modules count: " . count($firstCourse->getModules()) . "\n";
} else {
    echo "No courses found in database!\n";
}

echo "\nChecking Filter Logic (all null)...\n";
$filtered = $courseRepo->findByFilters(null, null, null);
echo "Filtered count: " . count($filtered) . "\n";
