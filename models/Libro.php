<?php

namespace app\models;

use yii\mongodb\ActiveRecord;
use MongoDB\BSON\ObjectId;
class Libro extends ActiveRecord
{
    public static function collectionName()
    {
        return 'libros';
    }

    public function getAutores()
    {
        return $this->hasMany(Autor::class, ['_id' => 'autores._id']);
    }

    public function attributes()
    {
        return ['_id','titulo', 'autores', 'anio_publicacion', 'descripcion'];
    }

    public function rules()
    {
        return [
            [['titulo', 'autores', 'anio_publicacion'], 'required'],
            [['titulo',  'descripcion'], 'string'],
            [['anio_publicacion'], 'integer'],
            ['autores', 'validateAutores'],
        ];
    }

    public function validateAutores($attribute, $params)
    {
        foreach ($this->$attribute as $autorId) {
            // Convertir el ID del autor a un objeto ObjectId
            try {
                $autorObjectId = new ObjectId($autorId);
            } catch (\Exception $e) {
                $this->addError($attribute, "Autor ID $autorId is not a valid ObjectId.");
                return;
            }
            
            if (Autor::findOne($autorObjectId) === null) {
                $this->addError($attribute, "Autor with ID $autorId does not exist.");
            }
        }
    }
}
