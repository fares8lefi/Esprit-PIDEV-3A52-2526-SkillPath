<?php

namespace App\Form;

use App\Entity\Course;
use App\Entity\Quiz;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuizType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Title',
                'attr' => ['class' => 'form-input', 'placeholder' => 'Quiz Name']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['class' => 'form-input', 'rows' => 4, 'placeholder' => 'Quiz Description']
            ])
            ->add('duree', IntegerType::class, [
                'label' => 'Duration (minutes)',
                'attr' => ['class' => 'form-input', 'placeholder' => 'Ex: 30']
            ])
            ->add('noteMax', IntegerType::class, [
                'label' => 'Max Score',
                'attr' => ['class' => 'form-input', 'placeholder' => 'Ex: 100']
            ])
            ->add('course', EntityType::class, [
                'class' => Course::class,
                'choice_label' => 'title',
                'label' => 'Associated Course',
                'placeholder' => '-- Select a course --',
                'required' => false,
                'attr' => ['class' => 'form-input']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Quiz::class,
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}
