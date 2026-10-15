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
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ItemType extends AbstractType
{
    private array $albums = [];

    private readonly string $defaultAlbum;

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

        // image entries added without a file are silently dropped (both when adding and editing an item);
        // this must run before the collection resizes itself, hence the higher priority
        $builder->get('images')->addEventListener(FormEvents::PRE_SUBMIT, $this->removeEmptyImages(...), 10);
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

    private function removeEmptyImages(FormEvent $event): void
    {
        $images = $event->getData();

        if (!\is_array($images)) {
            return;
        }

        foreach ($images as $key => $image) {
            // already stored images are submitted with their (read only) name, new entries have it set
            // by JavaScript only after the file has been chosen
            $hasFile = ($image['file'] ?? null) instanceof UploadedFile;
            $hasName = '' !== trim((string) ($image['name'] ?? ''));
            $hasToken = '' !== trim((string) ($image['uploadToken'] ?? ''));

            if (!$hasFile && !$hasName && !$hasToken) {
                unset($images[$key]);
            }
        }

        $event->setData($images);
    }
}
