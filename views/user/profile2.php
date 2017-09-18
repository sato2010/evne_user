<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use budyaga\cropper\Widget;
use budyaga\users\models\User;
use budyaga\users\components\AuthKeysManager;
use budyaga\users\UsersAsset;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \budyaga\users\models\User */

$this->title = Yii::t('users', 'Личный кабинет');
$this->params['breadcrumbs'][] = $this->title;
UsersAsset::register($this);
?>
<div class="site-profile">
    <h1><?= Html::encode($this->title) ?></h1>
    <div class="row">
        <div class="col-xs-12 col-md-7">
            <div class="panel panel-default">
                <div class="panel-heading"><?= Yii::t('users', 'Персональная информация')?></div>
                <div class="panel-body">
                    <?php $form = ActiveForm::begin(['id' => 'form-profile']); ?>
                    <?= $form->field($model, 'username') ?>
<!--                    --><?//= $form->field($model, 'sex')->dropDownList(User::getSexArray()); ?>
<!--                    --><?//= $form->field($model, 'photo')->widget(Widget::className(), [
//                        'uploadUrl' => Url::toRoute('/user/user/uploadPhoto'),
//                    ]) ?>

                    <div class="form-group">
                        <?= Html::submitButton(Yii::t('users', 'Сохранить'), ['class' => 'btn btn-primary', 'name' => 'profile-button']) ?>
                    </div>
                    <?php ActiveForm::end(); ?>
                    <div>

                    <?php $zapisi = \backend\models\Zapis::find()->where(['id_student' => Yii::$app->user->identity->id])->all(); ?>
                    <?php if($zapisi): ?>
                    <? foreach ($zapisi as $zapis): ?>
                    <?php
//                        $kurs = \backend\models\Kurs::findOne($zapis->id_student);
                        $zadaniya = \backend\models\Zadanie::getZadanie($zapis->id_kursa);
                        $novosti = \backend\models\Newskurs::getNewskurs($zapis->id_kursa);
                        $kurs = \backend\models\Kurs::find()->where(['id_kurs' => $zapis->id_kursa])->one();


                    ?>


                        <? if($zadaniya): ?>
                           <br><h4>Материалы и домашние задания по курсу <?php echo $kurs->kursname; ?></h4>
                          <ol>
                            <? foreach ($zadaniya as $zadanie): ?>

                              <li>  <a href="<?= $zadanie->text_zadanie ?> " download ><?= $zadanie->name_zadanie ?></a>     </li>
                            <? endforeach; ?>
                              </ol>
                        <? else: ?>
                            Задания не найдены
                        <? endif; ?>


                    <? endforeach; ?>
                    <? else: ?>
                        Записи не найдены
                    <? endif; ?>

                </div></div>
                РАСПИСАНИЕ
                <?php echo $kurs->raspisanie; ?> <br>
                ВЫХОДНЫЕ <?= $kurs->vuxodnie  ?>
            </div>
        </div>

        <div class="col-xs-12 col-md-5">
            <div class="panel panel-default">
                <div class="panel-heading"><?= Yii::t('users', 'Изменить пароль')?></div>
                <div class="panel-body">
                    <?php $form = ActiveForm::begin(['id' => 'form-password']); ?>
                    <?php if ($model->password_hash != '') : ?>
                        <?= $form->field($changePasswordForm, 'old_password')->passwordInput(); ?>
                    <?php endif;?>
                    <?= $form->field($changePasswordForm, 'new_password')->passwordInput(); ?>
                    <?= $form->field($changePasswordForm, 'new_password_repeat')->passwordInput(); ?>
                    <div class="form-group">
                        <?= Html::submitButton(Yii::t('users', 'Сохранить'), ['class' => 'btn btn-primary', 'name' => 'password-button']) ?>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading"><?= Yii::t('users', 'Изменить EMAIL')?></div>
                <div class="panel-body">
                    <?php $form = ActiveForm::begin(['id' => 'form-email']); ?>
                    <?= $form->field($changeEmailForm, 'new_email')->input('email'); ?>
                    <div class="form-group">
                        <?= Html::submitButton(Yii::t('users', 'Сохранить'), ['class' => 'btn btn-primary', 'name' => 'email-button']) ?>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading"><?= Yii::t('users', 'Новости по курсу')?></div>
                <div class="panel-body">

                    <?php $zapisi = \backend\models\Zapis::find()->where(['id_student' => Yii::$app->user->identity->id])->all(); ?>
                    <?php if($zapisi): ?>
                        <? foreach ($zapisi as $zapis): ?>
                            <?php
                            $kurs = \backend\models\Kurs::findOne($zapis->id_student);
                            $zadaniya = \backend\models\Zadanie::getZadanie($zapis->id_kursa);
                            $novosti = \backend\models\Newskurs::getNewskurs($zapis->id_kursa);


                            ?>

                            <? if($novosti): ?>
                                <br><h4>Новости по курсу</h4>
                                <ol>
                                    <? foreach ($novosti as $novost): ?>

                                        <li>  <a><?= $novost->title ?></a>     </li>
                                    <? endforeach; ?>
                                </ol>
                            <? else: ?>
                                Задания не найдены
                            <? endif; ?>
                        <? endforeach; ?>
                    <? else: ?>
                        Записи не найдены
                    <? endif; ?>


                    <!--                    --><?//= AuthKeysManager::widget([
//                        'baseAuthUrl' => ['/user/auth/index'],
//                    ]) ?>
                </div>
            </div>
        </div>
    </div>
</div>
