<?php

namespace app\controllers;

use Yii;
use yii\rest\ActiveController;
use app\models\Autor;
use app\models\Libro;
use yii\web\NotFoundHttpException;
use yii\filters\ContentNegotiator;
use yii\filters\VerbFilter;
use yii\web\Response;

use yii\filters\auth\HttpBearerAuth;

class AutorController extends ActiveController
{
    public $modelClass = 'app\models\Autor';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
        ];
        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::class,
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
            ],
        ];
        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'index' => ['GET'],
                'view' => ['GET'],
                'create' => ['POST'],
                'update' => ['PUT', 'PATCH'],
                'delete' => ['DELETE'],
            ],
        ];
        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index'], $actions['view'], $actions['create'], $actions['update'], $actions['delete']);
        return $actions;
    }

    public function actionIndex()
    {
        $autores = Autor::find()->all();
        $librosDeAutores = [];
    
        foreach ($autores as $autor) {
          
           
            $libros = Libro::find()->where(['autores' => $autor->_id->__toString()])->all();
            
        
       $autor->libros_escritos =$libros;
    
        }
       
        return $autores;
    }

    public function actionView($id)
    {
       // return $this->findModel($id);
        $autor = $this->findModel($id);
        // Buscar los libros asociados al autor
    $libros = Libro::find()->where(['autores' => $autor->_id->__toString()])->all();
    
    // Asignar los libros encontrados al autor
    $autor->libros_escritos = $libros;
    
    return $autor;
    }

    public function actionCreate()
    {
        $model = new Autor();
        $model->load(Yii::$app->request->getBodyParams(), '');

        if ($model->save()) {
            Yii::$app->response->setStatusCode(201);
            return $model;
        } else {
            Yii::$app->response->setStatusCode(422);
            return $model->getErrors();
        }
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->load(Yii::$app->request->getBodyParams(), '');

        if ($model->save()) {
            return $model;
        } else {
            Yii::$app->response->setStatusCode(422);
            return $model->getErrors();
        }
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model !== null) {
            $model->delete();
            Yii::$app->response->setStatusCode(204);
            return null;
        } else {
            throw new NotFoundHttpException('The requested autor does not exist.');
        }
    }

    protected function findModel($id)
    {
        if (($model = Autor::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested autor does not exist.');
        }
    }
}
