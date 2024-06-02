<?php

namespace app\models;

use yii\mongodb\ActiveRecord;

class Autor extends ActiveRecord
{
    

    public static function collectionName()
    {
        return ['libelulaback', 'autores'];
    }

    public function attributes()
    {
        return ['_id','nombre_completo', 'fecha_nacimiento', 'libros_escritos'];
    }

    public function rules()
    {
        return [
            [['nombre_completo', 'fecha_nacimiento'], 'required'],
            [['nombre_completo', 'fecha_nacimiento'], 'string'],
            [['nombre_completo'], 'unique'],
        ];
    }


}
