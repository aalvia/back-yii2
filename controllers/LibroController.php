<?php

namespace app\controllers;

use Yii;
use yii\rest\ActiveController;
use app\models\Libro;
use app\models\Autor;
use yii\web\NotFoundHttpException;
use yii\filters\ContentNegotiator;
use yii\filters\VerbFilter;
use yii\web\Response;

use yii\filters\auth\HttpBearerAuth;
class LibroController extends ActiveController
{
    public $modelClass = 'app\models\Libro';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::class,
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
            ],
        ];
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
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
        $libros = Libro::find()->all();

        // Recorrer cada libro y cargar los datos de los autores asociados
        foreach ($libros as $libro) {
            $autores = [];
            foreach ($libro->autores as $autorId) {
                // Buscar el autor por su ID y agregarlo al array de autores
                $autor = Autor::findOne($autorId);
                if ($autor !== null) {
                    $autores[] = $autor;
                }
            }
            // Asignar el array de autores al libro
            $libro->autores = $autores;
        }
    
        return $libros;
    }

    public function actionView($id)
    {
       // return $this->findModel($id);
        $libro = $this->findModel($id);

        // Cargar los datos de los autores asociados al libro
    $autores = [];
    foreach ($libro->autores as $autorId) {
        // Buscar el autor por su ID y agregarlo al array de autores
        $autor = Autor::findOne($autorId);
        if ($autor !== null) {
            $autores[] = $autor;
        }
    }
    // Asignar el array de autores al libro
    $libro->autores = $autores;

    return $libro;
    }

    public function actionCreate()
    {
        $model = new Libro();
        $model->load(Yii::$app->request->getBodyParams(), '');

        if ($model->validate() && $model->save()) {
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

        if ($model->validate() && $model->save()) {
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
            throw new NotFoundHttpException('The requested libro does not exist.');
        }
    }

    protected function findModel($id)
    {
        if (($model = Libro::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested libro does not exist.');
        }
    }
}
