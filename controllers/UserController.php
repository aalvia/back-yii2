<?php
namespace app\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\filters\auth\HttpBearerAuth;
use app\models\LoginForm;
use app\models\User;

class UserController extends ActiveController
{
    public $modelClass = 'app\models\User';

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // Añadir autenticación basada en token excepto para crear usuario y login
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'except' => ['create', 'login'],
        ];

        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();

        // Desactivar la acción create proporcionada por defecto
        unset($actions['create'], $actions['update']);
        return $actions;
    }

    public function actionCreate()
    {
        $model = new User();
        $params = Yii::$app->request->getBodyParams();
        $model->load($params, '');

        if ($model->validate()) {
            $model->password = $model->generatePasswordHash($model->password);
            if ($model->save()) {
                return $model;
            }
        }

        Yii::$app->response->statusCode = 400;
        return ['error' => 'Error creating user'];
    }

    public function actionLogin()
    {
        $model = new LoginForm();
        $model->load(Yii::$app->request->getBodyParams(), '');

        if ($model->login()) {
            $user = $model->getUser();
            if ($user) {
                $user->generateToken(); // Generar el token con expiración
                return ['token' => $user->access_token];
            }
        }

        Yii::$app->response->statusCode = 401;
        return ['error' => 'Authentication failed.'];
    }
    public function actionView($id)
    {
        return $this->findModel($id);
    }
    // Método para actualizar usuario
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $params = Yii::$app->request->getBodyParams();
        $model->load($params, '');

        // Verificar si la contraseña se está actualizando
        if (isset($params['password'])) {
            $model->password = $model->generatePasswordHash($params['password']);
        }

        // Validar y guardar el modelo
        if ($model->save()) {
            return $model;
        } else {
            Yii::$app->response->statusCode = 400;
            return ['error' => 'Error updating user'];
        }
    }

    // Método para eliminar usuario
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if ($model->delete()) {
            Yii::$app->response->statusCode = 204;
            return ['success' => 'User deleted'];
        }

        Yii::$app->response->statusCode = 400;
        return ['error' => 'Error deleting user'];
    }

    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        throw new \yii\web\NotFoundHttpException("User with ID $id not found");
    }
}
