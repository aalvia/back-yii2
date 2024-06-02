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

/**
 * LibroController handles the CRUD actions for Libro model.
 */
class LibroController extends ActiveController
{
    public $modelClass = 'app\models\Libro';

    /**
     * Configures behaviors for the controller.
     * 
     * @return array The behaviors configuration.
     */
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

    /**
     * Configures actions for the controller.
     * 
     * @return array The actions configuration.
     */
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index'], $actions['view'], $actions['create'], $actions['update'], $actions['delete']);
        return $actions;
    }

    /**
     * Lists all Libro models.
     * 
     * @return array The list of Libro models.
     */
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

    /**
     * Displays a single Libro model.
     * 
     * @param string $id The ID of the model to be displayed.
     * @return Libro The loaded model.
     * @throws NotFoundHttpException if the model cannot be found.
     */
    public function actionView($id)
    {
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

    /**
     * Creates a new Libro model.
     * 
     * @return Libro|array The created model or validation errors.
     */
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

    /**
     * Updates an existing Libro model.
     * 
     * @param string $id The ID of the model to be updated.
     * @return Libro|array The updated model or validation errors.
     * @throws NotFoundHttpException if the model cannot be found.
     */
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

    /**
     * Deletes an existing Libro model.
     * 
     * @param string $id The ID of the model to be deleted.
     * @return void
     * @throws NotFoundHttpException if the model cannot be found.
     */
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

    /**
     * Finds the Libro model based on its primary key value.
     * 
     * @param string $id The ID of the model to be found.
     * @return Libro The loaded model.
     * @throws NotFoundHttpException if the model cannot be found.
     */
    protected function findModel($id)
    {
        if (($model = Libro::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested libro does not exist.');
        }
    }
}
