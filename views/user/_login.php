<?php

use backend\models\Kurs;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use budyaga\users\components\AuthChoice;
$model = new LoginForm();
?>
<div class="modal-body">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
    <h2 class="modal-title" id="myModalLabel">Войдитe</h2>



    <?php $form = ActiveForm::begin(['action' => '/login', 'id' => 'login-form']); ?>



    <div class="form-group float-label-control">
        <?= $form->field($model, 'email', [
            'template' => "{input}{label}{hint}{error}"
        ])->textInput(['placeholder' => 'Эл. почта', 'class' => 'form-control empty', 'data-error' => 'Сообщение об ошибке', 'required' => 'required']) ?>


    </div>

    <div class="form-group float-label-control last-form-group">
        <?= $form->field($model, 'password', [
            'template' => "{input}{label}{hint}{error}"
        ])->textInput(['placeholder' => 'Пароль', 'class' => 'form-control empty', 'data-error' => 'Сообщение об ошибке', 'required' => 'required']) ?>

    </div>

    <?= $form->field($model, 'rememberMe')->checkbox() ?>
    <div style="color:#999;margin:1em 0">
        <?= Yii::t('users', 'YOU_CAN_RESET_PASSWORD', ['url' => Url::toRoute('/user/user/request-password-reset')])?>
    </div>
    <div class="form-group">
        <?= Html::submitButton(Yii::t('users', 'Войти'), ['class' => 'enter-btn btn btn-default btn-block', 'name' => 'login-button']) ?>
    </div>
    <?php ActiveForm::end(); ?>
    <p><?= Yii::t('users', 'Или с помощью социальной сети')?></p>

    <?php $d = Yii::getAlias('@vendor/bodyaga'); ?>
    <?= AuthChoice::widget([
        'baseAuthUrl' => ['/user/auth/index'],
        'clientCssClass' => 'btn-group socials-btn-group'
    ]) ?>
</div>