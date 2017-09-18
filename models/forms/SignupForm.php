<?php
namespace budyaga\users\models\forms;

use budyaga\users\models\User;
// use common\models\User;
use yii\base\Model;
use Yii;

/**
 * Signup form
 */
class SignupForm extends Model
{
    public $username;
    public $email;
    public $password;
    public $password_repeat;
    public $sex;
    public $photo;
    public $phone;
    public $description;
    public $kurs;
    public $about;
    public $user_files;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['username', 'filter', 'filter' => 'trim'],
            ['username', 'required'],
            ['username', 'string', 'min' => 2, 'max' => 255],
            ['username', 'unique', 'targetClass' => '\budyaga\users\models\User', 'message' => Yii::t('users', 'THIS_USERNAME_ALREADY_TAKEN')],

            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'unique', 'targetClass' => '\budyaga\users\models\User', 'message' => Yii::t('users', 'THIS_EMAIL_ALREADY_TAKEN')],
            ['sex', 'in', 'range' => [User::SEX_MALE, User::SEX_FEMALE]],
            ['photo', 'safe'],

            [['password', 'password_repeat'], 'required'],
            [['password', 'password_repeat'], 'string', 'min' => 6],
            ['password_repeat', 'compare', 'compareAttribute' => 'password'],
            ['phone', 'string', 'max' => 255],
            [['description'], 'file', 'extensions' => 'pdf, doc, png, jpg, jpeg', 'maxFiles' => 4],
            ['kurs', 'string', 'max' => 255],
            ['about', 'string', 'max' => 255],
            [['user_files'], 'file', 'maxSize' => 1024 * 1024 * 3, 'extensions' => 'pdf, doc, docx'],
        ];
    }


    public function attributeLabels()
    {
        return [
            'username' => Yii::t('users', 'Имя и фамилия'),
            'email' => Yii::t('users', 'Эл. почта'),
            'sex' => Yii::t('users', 'Пол'),
            'password' => Yii::t('users', 'Пароль'),
            'password_repeat' => Yii::t('users', 'Повторите пароль'),
            'photo' => Yii::t('users', 'Фото'),
            'phone' => Yii::t('users', 'Телефон'),
            'description' => Yii::t('users', 'Примеры работ'),
            'kurs' => Yii::t('users', 'Где Вы хотите учиться?'),
            'about' => Yii::t('users', 'Расскажите немного о себе')
        ];
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        if ($this->validate()) {
            $user = new User();
            $user->attributes = $this->attributes;
            $user->status = User::STATUS_NEW;
            $user->setPassword($this->password);
            $user->generateAuthKey();
            if ($user->save()) {
                return $user;
            }
        }
        return null;
    }

}
