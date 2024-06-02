<?php
namespace app\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\filters\auth\HttpBearerAuth;
use app\models\LoginForm;
use app\models\User;

/**
 * UserController handles the CRUD actions for User model.
 */
class UserController extends ActiveController
{
    public $modelClass = 'app\models\User';

    /**
     * Configures behaviors for the controller.
     * 
     * @return array The behaviors configuration.
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // Add token-based authentication except for create and login actions
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'except' => ['create', 'login'],
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

        // Disable the default create action
        unset($actions['create'], $actions['update']);
        return $actions;
    }

    /**
     * Creates a new User model.
     * 
     * @return User|array The created model or validation errors.
     */
    public function actionCreate()
    {
        $model = new User();
        $params = Yii::$app->request->getBodyParams();
        $model->load($params, '');

        if ($model->validate()) {
            $model->password = $model->generatePasswordHash($model->password);
            if ($model->save()) {
                Yii::$app->response->statusCode = 201;
                return $model;
            } else {
                Yii::$app->response->statusCode = 500;
                return ['error' => 'Error saving user'];
            }
        } else {
            Yii::$app->response->statusCode = 422;
            return $model->getErrors();
        }
    }

    /**
     * Logs in a user.
     * 
     * @return array The token or authentication failure message.
     */
    public function actionLogin()
    {
        $model = new LoginForm();
        $model->load(Yii::$app->request->getBodyParams(), '');

        if ($model->login()) {
            $user = $model->getUser();
            if ($user) {
                $user->generateToken(); // Generate token with expiration
                return ['token' => $user->access_token];
            }
        }

        Yii::$app->response->statusCode = 401;
        return ['error' => 'Authentication failed.'];
    }

    /**
     * Displays a single User model.
     * 
     * @param int $id The ID of the user.
     * @return User The loaded model.
     * @throws NotFoundHttpException if the model cannot be found.
     */
    public function actionView($id)
    {
        return $this->findModel($id);
    }

    /**
     * Updates an existing User model.
     * 
     * @param int $id The ID of the user to be updated.
     * @return User|array The updated model or validation errors.
     * @throws NotFoundHttpException if the model cannot be found.
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $params = Yii::$app->request->getBodyParams();
        $model->load($params, '');

        // Check if the password is being updated
        if (isset($params['password'])) {
            $model->password = $model->generatePasswordHash($params['password']);
        }

        // Validate and save the model
        if ($model->save()) {
            return $model;
        } else {
            Yii::$app->response->statusCode = 400;
            return ['error' => 'Error updating user'];
        }
    }

    /**
     * Deletes an existing User model.
     * 
     * @param int $id The ID of the user to be deleted.
     * @return array The success message or error message.
     * @throws NotFoundHttpException if the model cannot be found.
     */
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

    /**
     * Finds the User model based on its primary key value.
     * 
     * @param int $id The ID of the user.
     * @return User The loaded model.
     * @throws NotFoundHttpException if the model cannot be found.
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        throw new \yii\web\NotFoundHttpException("User with ID $id not found");
    }
}
