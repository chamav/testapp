<?php
/**
 * Created by PhpStorm.
 * User: andrp
 * Date: 30.08.2016
 * Time: 15:52
 */

namespace App\Transformers;

use Illuminate\Database\Eloquent\Model;
use Themsaid\Transformers\AbstractTransformer;
use App\UserFile;


class UserProfileTransformer extends AbstractTransformer
{
    public function transformModel(Model $instance)
    {
        $result = $instance->toArray();
        $result['city'] = $instance->city->name_ru;
        return $result;
    }
}