<?php

namespace App\Form;

use App\Entity\Question;
use App\Entity\Quiz;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('enonce', TextareaType::class, [
                'label' => 'Énoncé de la question',
                'attr' => ['class' => 'form-input', 'rows' => 3, 'placeholder' => 'Saisissez la question...']
            ])
            ->add('choixA', TextType::class, [
                'label' => 'Choix A',
                'attr' => ['class' => 'form-input', 'placeholder' => 'Réponse A']
            ])
            ->add('choixB', TextType::class, [
                'label' => 'Choix B',
                'attr' => ['class' => 'form-input', 'placeholder' => 'Réponse B']
            ])
            ->add('choixC', TextType::class, [
                'label' => 'Choix C',
                'attr' => ['class' => 'form-input', 'placeholder' => 'Réponse C']
            ])
            ->add('choixD', TextType::class, [
                'label' => 'Choix D',
                'attr' => ['class' => 'form-input', 'placeholder' => 'Réponse D']
            ])
            ->add('bonneReponse', ChoiceType::class, [
                'label' => 'Bonne Réponse',
                'choices' => [
                    'A' => 'A',
                    'B' => 'B',
                    'C' => 'C',
                    'D' => 'D',
                ],
                'placeholder' => '-- Choisir --',
                'attr' => ['class' => 'form-input']
            ])
            ->add('points', IntegerType::class, [
                'label' => 'Points',
                'attr' => ['class' => 'form-input', 'placeholder' => 'Ex: 5']
            ])
            ->add('quiz', EntityType::class, [
                'class' => Quiz::class,
                'choice_label' => 'titre',
                'label' => 'Quiz',
                'attr' => ['class' => 'form-input']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Question::class,
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}
