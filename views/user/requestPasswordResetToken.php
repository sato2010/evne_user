<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */

$this->title = Yii::t('users', 'REQUEST_PASSWORD_RESET');
$this->params['breadcrumbs'][] = $this->title;
?>
<div id="request-pass-reset" class="main-content page-content">
    <section class="banner-section home-page-banner">
        <div class="container">
            <h2 class="modal-title">
                <?= Html::encode($this->title) ?>

            </h2>

            <div class="row">
                <div class="col-lg-4" style="margin-top: 20px;">
                    <?php $form = ActiveForm::begin(['id' => 'request-password-reset-form']); ?>
                    <div class="form-group float-label-control">
                        <?= $form->field($model, 'email', [
                        'template' => "{input}{label}{hint}{error}"
                        ])->textInput(['class' => 'form-control empty']) ?>
                    </div>

                    <?= $form->field($model, 'reCaptcha', [
                        'template' => "{input}{label}{hint}{error}"
                     ])->widget(
                        \himiklab\yii2\recaptcha\ReCaptcha::className(),
                        //['siteKey' => '6Lfp9SwUAAAAALMxPtg0ie1Vae6x_D3kDDIN55MQ']
                        ['widgetOptions' =>
                            [
                                'class' => 'google-captcha',
                            ]
                        ]
                    )->label(false) ?>
                    <div class="form-group">
                        <?= Html::submitButton(Yii::t('users', 'SEND'), ['class' => 'enter-btn btn btn-default btn-block']) ?>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </section>
</div>
