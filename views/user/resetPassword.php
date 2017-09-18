<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \frontend\models\ResetPasswordForm */

$this->title = Yii::t('users', 'RESET_PASSWORD');
$this->params['breadcrumbs'][] = $this->title;
?>
<div id="reset-password" class="main-content page-content">
    <section class="banner-section home-page-banner">
        <div class="container col-centered">
            <div class="row">
                <h2 class="modal-title">
                    <?= Html::encode($this->title) ?>

                </h2>

                <div class="col-lg-4" style="margin-top: 20px;">
                    <?php $form = ActiveForm::begin(['id' => 'reset-password-form']); ?>
                    <div class="form-group float-label-control">
                        <?= $form->field($model, 'password', [
                            'template' => "{input}{label}{hint}{error}"
                        ])->passwordInput(['class' => 'form-control empty']) ?>
                    </div>
                    <div class="form-group float-label-control">
                        <?= $form->field($model, 'password_repeat',[
                            'template' => "{input}{label}{hint}{error}"
                        ])->passwordInput(['class' => 'form-control empty']) ?>
                    </div>
                    <div class="form-group">
                        <?= Html::submitButton(Yii::t('users', 'SAVE'), ['class' => 'enter-btn btn btn-default btn-block']) ?>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </section>
</div>
