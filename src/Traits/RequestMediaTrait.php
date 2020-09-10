<?php

namespace Okami101\LaravelAdmin\Traits;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\FileAdder\FileAdder;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\MediaCollection\MediaCollection;
use Spatie\MediaLibrary\Models\Media;

/**
 * Trait RequestMediaTrait
 *
 * @mixin Model
 */
trait RequestMediaTrait
{
    use HasMediaTrait;

    /**
     * Auto attach media via request
     */
    public static function bootRequestMediaTrait(): void
    {
        static::saved(static function (Model $model) {
            $model->registerMediaCollections();

            collect($model->mediaCollections)->each(function (MediaCollection $collection) use ($model) {
                /**
                 * Media to delete
                 */
                $ids = request()->input("{$collection->name}_delete");
                $model->getMedia($collection->name)->filter(function (Media $media) use ($ids) {
                    return in_array($media->id, is_array($ids) ? $ids : [$ids], false);
                })->each->delete();

                /**
                 * Media to add
                 */
                if (request()->hasFile($collection->name)) {
                    $model->addMultipleMediaFromRequest([$collection->name])
                        ->each(function (FileAdder $file) use ($collection) {
                            $file->toMediaCollection($collection->name);
                        });
                }
            });
        });
    }
}
