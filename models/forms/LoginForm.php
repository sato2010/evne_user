<?php
namespace budyaga\users\models\forms;

use common\models\UserEvne;
use Yii;
use yii\base\Model;
use budyaga\users\models\User;

/**
 * Login form
 */
class LoginForm extends Model
{
    public $email;
    public $password;
    public $rememberMe = true;

    private $_user = false;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['email', 'password'], 'required'],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword'],
            [['email'], 'email'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            if(isset($user) && $user->status == User::STATUS_NEW ){
                $this->addError($attribute, 'Ваш аккаунт не активирован.');
            }
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Неверно указан логин или пароль.');

            }
        }
    }

    public function attributeLabels()
    {
        return [
            'email' => Yii::t('users', 'Эл. почта или имя'),
            'password' => Yii::t('users', 'Пароль'),
            'rememberMe' => Yii::t('users', 'Запомнить меня'),
        ];
    }
    /**
     * Logs in a user using the provided username and password.
     *
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
        } else {
            return false;
        }
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = User::findByEmailOrUserName($this->email);
        }

        if ($this->_user === false || is_null($this->_user)) {
            $this->_user = UserEvne::findByStatusNew($this->email);
        }

        return $this->_user;
    }
}
