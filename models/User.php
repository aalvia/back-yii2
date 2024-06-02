<?php
namespace app\models;

use yii\mongodb\ActiveRecord;
use yii\web\IdentityInterface;
use Yii;
use yii\base\Security;

class User extends ActiveRecord implements IdentityInterface
{
    public static function collectionName()
    {
        return ['libelulaback', 'user'];
    }

    public function attributes()
    {
        return [
            '_id',
            'username',
            'email',
            'password',
            'access_token',
            'token_expiration', // Añadir este campo
        ];
    }

    public static function findIdentity($id)
    {
        return static::findOne(['_id' => $id]);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        $user = static::findOne(['access_token' => $token]);
        if ($user) {
            if (empty($user->token_expiration)) {
                // Tratar como expirado si token_expiration es null o vacío
                return null;
            }

            $expirationTime = strtotime($user->token_expiration);
            // Verificar si el token ha expirado
            if ($expirationTime === false || $expirationTime < time()) {
                return null;
            }
            return $user;
        }
        return null;
    }

    public function getId()
    {
        return (string)$this->_id;
    }

    public function getAuthKey()
    {
        return null; 
    }

    public function validateAuthKey($authKey)
    {
        return false; 
    }

    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
    }

    public function generatePasswordHash($password)
    {
        $security = new Security();
        return $security->generatePasswordHash($password);
    }

    public function validatePassword($password)
    {
        $security = new Security();
        return $security->validatePassword($password, $this->password);
    }

    public function generateToken()
    {
        $this->access_token = Yii::$app->security->generateRandomString();
        $this->token_expiration = date('Y-m-d H:i:s', strtotime('+30 minutes'));
        $this->save(false);
    }

    public function rules()
    {
        return [
            [['username', 'email', 'password'], 'required', 'message' => 'Este campo no puede estar vacío.'],
            [['username', 'email', 'password', 'access_token', 'token_expiration'], 'string'],
            [['email'], 'email', 'message' => 'Formato de correo electrónico inválido.'],
            [['username', 'email'], 'unique', 'message' => 'Este usuario o correo electrónico ya existe en la base de datos.'],
        ];
    }
    
    
}
