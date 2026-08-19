<?php

namespace App\Services;

use App\Support\MediaStorage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;

class MediaCardService
{
    /**
     * @param  class-string<Model>  $modelClass
     * @param  array<string, mixed>  $data
     */
    public function create(string $modelClass, array $data, ?UploadedFile $photo, string $directory): Model
    {
        if ($photo) {
            $data['photo_path'] = MediaStorage::store($photo, $directory);
        }

        return $modelClass::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Model $model, array $data, ?UploadedFile $photo, string $directory): Model
    {
        $oldPath = null;

        if ($photo) {
            $oldPath = (string) $model->getAttribute('photo_path');
            $data['photo_path'] = MediaStorage::store($photo, $directory);
        }

        $model->update($data);
        MediaStorage::delete($oldPath);

        return $model;
    }

    public function delete(Model $model): void
    {
        MediaStorage::delete((string) $model->getAttribute('photo_path'));
        $model->delete();
    }
}
