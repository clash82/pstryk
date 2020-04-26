<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ItemType extends AbstractType
{
    /**
     * @var array
     */
    private $albums = [];

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $albums = $parameterBag->get('app')['albums'];

        foreach ($albums as $albumId => $albumSettings) {
            $this->albums[sprintf('%s (%s)', $albumSettings['title'], $albumId)] = $albumId;
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options = []): void
    {
        $builder
            ->add('album', ChoiceType::class, [
                'label' => 'Album',
                'placeholder' => '- wybierz album -',
                'choices' => $this->albums,
            ])
            ->add('title', TextType::class, [
                'label' => 'Tytuł',
            ])
            ->add('slug', TextType::class, [
                'label' => 'Slug',
                'required' => false,
                'attr' => [
                    'help' => 'pozostaw pole puste, aby wygenerować nową wartość na podstawie tytułu',
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Opis',
                'required' => false,
                'attr' => [
                    'class' => 'form-textarea',
                ],
            ])
            ->add('date', DateTimeType::class, [
                'widget' => 'single_text',
                'format' => 'YYYY-MM-dd HH:mm',
                'html5' => false,
                'label' => 'Data',
                'attr' => [
                    'class' => 'form-datetimepicker',
                ],
            ])
            ->add('latitude', NumberType::class, [
                'label' => 'Szerokość geograficzna',
                'required' => false,
                'scale' => 8,
                'attr' => [
                    'help' => 'latitude',
                 ],
            ])
            ->add('longitude', NumberType::class, [
                'label' => 'Długość geograficzna',
                'required' => false,
                'scale' => 8,
                'attr' => [
                    'help' => 'longitude',
                ],
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Aktywny',
                'required' => false,
                'attr' => [
                    'merge_label' => true,
                ],
            ])
            ->add('save', SubmitType::class)
            ->add('back', ButtonType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Item',
            'creation_type' => null,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'item';
    }
}
