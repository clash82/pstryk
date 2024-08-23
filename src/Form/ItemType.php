<?php declare(strict_types=1);

namespace App\Form;

use App\Entity\Item;
use App\Provider\AdminSettingsProvider;
use App\Provider\AlbumProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ItemType extends AbstractType
{
    private array $albums = [];

    private readonly ?string $defaultAlbum;

    public function __construct(AlbumProvider $albumProvider, AdminSettingsProvider $adminSettingsProvider)
    {
        $albums = $albumProvider->getAll();

        foreach ($albums as $album) {
            $this->albums[\sprintf('%s (%s)', $album->getTitle(), $album->getSlug())] = $album->getSlug();
        }

        $this->defaultAlbum = $adminSettingsProvider->getAlbum();
    }

    public function buildForm(FormBuilderInterface $builder, array $options = []): void
    {
        /** @var Item $data */
        $data = $builder->getData();

        $builder
            ->add('album', ChoiceType::class, [
                'label' => 'Album',
                'placeholder' => '- wybierz album -',
                'choices' => $this->albums,
                'data' => $data->getAlbum() ?? $this->defaultAlbum,
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
            ->add('images', CollectionType::class, [
                'entry_type' => ImageType::class,
                'label' => 'Zdjęcia',
                'prototype' => true,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'required' => false,
                'delete_empty' => true,
                'attr' => [
                    'class' => 'images-collection',
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
            'data_class' => Item::class,
            'creation_type' => null,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'item';
    }
}
