<?php

namespace App\Form;

use App\Entity\Cours;
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
            ->add('titre', TextType::class, [
                'label' => 'Titre',
                'attr' => ['class' => 'form-input', 'placeholder' => 'Nom du quiz']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['class' => 'form-input', 'rows' => 4, 'placeholder' => 'Description du quiz']
            ])
            ->add('duree', IntegerType::class, [
                'label' => 'Durée (minutes)',
                'attr' => ['class' => 'form-input', 'placeholder' => 'Ex: 30']
            ])
            ->add('noteMax', IntegerType::class, [
                'label' => 'Note maximale',
                'attr' => ['class' => 'form-input', 'placeholder' => 'Ex: 100']
            ])
            ->add('cours', EntityType::class, [
                'class' => Cours::class,
                'choice_label' => 'titre',
                'label' => 'Cours associé',
                'placeholder' => '-- Sélectionner un cours --',
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
