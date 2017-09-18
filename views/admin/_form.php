<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use budyaga\cropper\Widget;
use budyaga\users\models\User;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model budyaga\users\models\User */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="user-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'username')->textInput(['maxlength' => 255]) ?>

    <?= $form->field($model, 'email')->textInput(['maxlength' => 255]) ?>

    <?= $form->field($model, 'photo')->widget(Widget::className(), [
        'uploadUrl' => Url::toRoute('/user/user/uploadPhoto'),
    ]) ?>

    <?= $form->field($model, 'sex')->dropDownList(User::getSexArray()); ?>

    <? if(Yii::$app->request->get('is_teacher') == 'Y'): ?>
    <div class="hide">
        <?= $form->field($model, 'status')->textInput(['maxlength' => 255, 'value' => 2]) ?>
    </div>
    <? else: ?>
    <?= $form->field($model, 'status')->dropDownList(User::getStatusArray()); ?>
    <? endif ?>
    <?= $form->field($model, 'phone')->textInput(['maxlength' => 255]) ?>

    <?= $form->field($model, 'about')->textarea(['maxlength' => 255]) ?>

    <? if($model->isNewRecord): ?>
        <p>Установите пароль для пользователя</p>
    <? else: ?>
        <p>Изменить пароль для пользователя(опционально)</p>
    <? endif ?>
    <?= $form->field($model, 'password')->textInput(['maxlength' => 255]) ?>

<!--    --><?//= $form->field($model, 'description')->textInput(['maxlength' => 255]) ?>

    <? if(Yii::$app->request->get('is_teacher') == 'Y'): ?>
        <div class="hide">
        <?= $form->field($model, 'kurs')->textInput(['maxlength' => 255, 'value' => 13]) ?>
        </div>
    <? else: ?>
    <?= $form->field($model, 'kurs')->dropDownList(ArrayHelper::map($kurs, 'id_kurs', 'kursname')) ?>
    <? endif ?>
    <? if(Yii::$app->request->get('is_teacher') == 'Y'): ?>
        <div class="hide">
            <?= $form->field($model, 'teacher')->textInput(['maxlength' => 255, 'value' => 1]) ?>
        </div>
    <? else: ?>
        <?= $form->field($model, 'teacher')->checkbox() ?>
    <? endif ?>

<!--    --><?//= $form->field($model, 'description')->dropDownList(ArrayHelper::map($resume, 'id', 'link')) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('users', 'CREATE') : Yii::t('users', 'UPDATE'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
