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

/**
 * AutorController handles the CRUD actions for Autor model.
 */
class AutorController extends ActiveController
{
    public $modelClass = 'app\models\Autor';

    /**
     * Configures behaviors for the controller.
     * 
     * @return array The behaviors configuration.
     */
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
     * Lists all Autor models.
     * 
     * @return array The list of Autor models.
     */
    public function actionIndex()
    {
        $autores = Autor::find()->all();
    
        foreach ($autores as $autor) {
            $libros = Libro::find()->where(['autores' => $autor->_id->__toString()])->all();
            $autor->libros_escritos = $libros;
        }
       
        return $autores;
    }

    /**
     * Displays a single Autor model.
     * 
     * @param string $id The ID of the model to be displayed.
     * @return Autor The loaded model.
     * @throws NotFoundHttpException if the model cannot be found.
     */
    public function actionView($id)
    {
        $autor = $this->findModel($id);
        $libros = Libro::find()->where(['autores' => $autor->_id->__toString()])->all();
        $autor->libros_escritos = $libros;
        
        return $autor;
    }

    /**
     * Creates a new Autor model.
     * 
     * @return Autor|array The created model or validation errors.
     */
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

    /**
     * Updates an existing Autor model.
     * 
     * @param string $id The ID of the model to be updated.
     * @return Autor|array The updated model or validation errors.
     * @throws NotFoundHttpException if the model cannot be found.
     */
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

    /**
     * Deletes an existing Autor model.
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
        } else {
            throw new NotFoundHttpException('The requested autor does not exist.');
        }
    }

    /**
     * Finds the Autor model based on its primary key value.
     * 
     * @param string $id The ID of the model to be found.
     * @return Autor The loaded model.
     * @throws NotFoundHttpException if the model cannot be found.
     */
    protected function findModel($id)
    {
        if (($model = Autor::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested autor does not exist.');
        }
    }
}
