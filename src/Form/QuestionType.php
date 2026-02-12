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
                'label' => 'Enoncé',
                'attr' => ['class' => 'form-control']
            ])
            ->add('choixA', TextType::class, [
                'label' => 'Choix A',
                'attr' => ['class' => 'form-control']
            ])
            ->add('choixB', TextType::class, [
                'label' => 'Choix B',
                'attr' => ['class' => 'form-control']
            ])
            ->add('choixC', TextType::class, [
                'label' => 'Choix C',
                'attr' => ['class' => 'form-control']
            ])
            ->add('choixD', TextType::class, [
                'label' => 'Choix D',
                'attr' => ['class' => 'form-control']
            ])
            ->add('bonneReponse', ChoiceType::class, [
                'label' => 'Bonne Réponse',
                'choices' => [
                    'A' => 'A',
                    'B' => 'B',
                    'C' => 'C',
                    'D' => 'D',
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('points', IntegerType::class, [
                'label' => 'Points',
                'attr' => ['class' => 'form-control']
            ])
            // We usually set the quiz via controller when adding from a quiz page, avoiding the need to select it here if coming from a context.
            // But if we want to allow changing it or adding standalone, we can keep it.
            // For now, I will omit it here assuming we pass it or it's implicitly handled, 
            // OR I can add it but disable it if passed via options.
            // Let's add it generic for now.
             ->add('quiz', EntityType::class, [
                'class' => Quiz::class,
                'choice_label' => 'titre',
                'attr' => ['class' => 'form-control']
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
