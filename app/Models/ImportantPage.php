<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportantPage extends Model
{
    public const PAGES = [
        'polityka-prywatnosci' => 'Polityka prywatności',
        'regulamin-sklepu' => 'Regulamin sklepu',
        'regulamin-zwrotow' => 'Regulamin zwrotów',
    ];

    protected $fillable = ['slug', 'body', 'image_path'];
}
