<?php
setlocale(LC_ALL, 'ru_RU.UTF-8');

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
$html = '';
$this->title = Yii::t('users', 'Личный кабинет - EVNE Academy');
$this->params['breadcrumbs'][] = $this->title;
UsersAsset::register($this);
$oneDay = 86400;
$js = <<<JS
var droppedFiles = false;
JS;
$this->registerJs($js, \yii\web\View::POS_HEAD);

$js = <<<JS
var formData;
$(document).on('change', 'input[type=file]', function (e) {
    if(droppedFiles){
        droppedFiles = false;
    }
    var data = $('#form-request-zadanie').data("yiiActiveForm");
    data.attributes[2].validate = 
    function (attribute, value, messages, deferred, form) {
        yii.validation.required(value, messages, {"message":"Необходимо заполнить «Файлы»."});
        yii.validation.file(attribute, messages, {
            "message":"Загрузка файла не удалась.",
            "skipOnEmpty":false,
            "uploadRequired":"Загрузите файл.",
            "mimeTypes":[],
            "wrongMimeType":"Разрешена загрузка файлов только со следующими MIME-типами1: .",
            "extensions":["pdf","doc","docx","png","jpg","jpeg","zip","rar","tar.gz","psd","scatch"],
            "wrongExtension":"Разрешена загрузка файлов только со следующими расширениями: pdf, doc, docx, png, jpg, jpeg, zip, rar, tar.gz, psd, scatch.",
            "maxSize":314572800,
            "tooBig":"Файл «{file}» слишком большой. Размер не должен превышать 300 МиБ.","maxFiles":4,"tooMany":"Вы не можете загружать более 4 файлов."
        });
    }
    
    $('#form-request-zadanie').yiiActiveForm('validateAttribute', true);
    var filename = $('input[type=file]').val().split("\\\").pop();
    $('.drop-download-box').hide();
    $('.progress').show();
    $('#uploaded-link .file').html('<i></i>' + filename).append("<span class='file-close'></span>");
    
});

$(document).on('afterValidate', '#form-request-zadanie', function (e) {
    var p_error = $('input[type=file]').next().text();
    if(p_error){
        $('#help-error-file').text(p_error);
    }else{
        $('#help-error-file').text('');
    }
    
});

$(document).on('click', 'span.file-close', function (e) {
    $('.drop-download-box').show();
    $('#uploaded-link .file').html('');
    $('input[type=file]').val('');
    $('.progress').hide();
    $('#help-error-file').text('');
});

$(document).on('beforeSubmit', '#form-request-zadanie', function (e) {
    e.preventDefault();
    var zadanie_id = $(document).find('a.send-request-data').data('id');
    var errors = false;
    var re = /(\.pdf|\.doc|\.docx|\.png|\.jpg|\.jpeg|\.zip|\.rar|\.tar.gz|\.psd|\.scatch)$/i;
    var idc = $(document).find('#requestzadanie-id_zadanie').val();
    var wrongMime = "Разрешена загрузка файлов только со следующими MIME-типами: pdf, doc, docx, png, jpg, jpeg, zip, rar, tar.gz, psd, scatch";
    var wrongSize = "Размер не должен превышать 300 МиБ."
    var progressBar = $('#progressbar');
    //var res = $('#form-request-zadanie').yiiActiveForm('validate', true);
    
    var that = $(this);
    formData = new FormData(that.get(0));
    
    if(droppedFiles){
        if(!re.exec(droppedFiles.name)){
            $('#help-error-file').text(wrongMime);
            errors = true;
        }
        if(droppedFiles.size > 314572800){
            $('#help-error-file').text(wrongSize);
            errors = true;
        }
        if(!errors){
            formData.append( 'RequestZadanie[files_data]', droppedFiles );
        }
    }
    
    if(!errors){
    
    $.ajax({
      url: '/send-request/' + zadanie_id, //that.attr('action'),
      type: that.attr('method'),
      contentType: false,
      processData: false,
      data: formData,
      dataType: 'json',
      xhr: function(){
        var xhr = $.ajaxSettings.xhr(); 
        xhr.upload.addEventListener('progress', function(evt) { 
          if(evt.lengthComputable) { 
            var percentComplete = Math.ceil(evt.loaded / evt.total * 100);
            //progressBar.val(percentComplete).text('Загружено ' + percentComplete + '%');
            progressBar.css('width', percentComplete + '%');
          }
        }, false);
        return xhr;
      },
      success: function(json){
        if(json){
          $('.content.sendRequestZadanieForm').html('');
          $.ajax({
            url: '/gethistory',
            type: 'POST',
            dataType: 'json',
            data: {id: idc},
            success: function(data) {
                if(data.status == true) {
                    $('.send-request-data').hide();
                    $('.historyLog').removeClass('loading').removeClass('nf').find('.data').html(data.html);
                }
                else {
                    $('.send-request-data').show();
                    $('.historyLog').removeClass('loading').addClass('nf').find('.data').html('');
                }
            }
        });
          //$('.send-request-data').hide();
          //$('.historyLog').removeClass('loading').removeClass('nf').find('.data').html(data.html);
        }
      }
    });
    }
    return false;
  });

JS;

$this->registerJs($js, \yii\web\View::POS_END);


?>


    <div class="user-page-content page-content">
        <div class="user-page_heading">
            <div class="container">
                <div class="row">
                    <div class="col-n1 col-lg-9 col-md-12 col-sm-12">
                        <div class="w-content">
                            <p class="user-name">
                                <?php foreach (explode(' ', Html::encode(Yii::$app->user->identity->username)) as $char):?>
                                <span><?= $char ?></span>

                                <?php endforeach; ?>
                            </p>
                        </div>
                        <!-- /.w-content -->
                    </div>
                    <?php $zapisi = \backend\models\Zapis::find()->where(['id_student' => Yii::$app->user->identity->id])->all(); ?>
                    <? foreach ($zapisi as $zapis):
                    //                        $kurs = \backend\models\Kurs::findOne($zapis->id_student);
                    $zadaniya = \backend\models\Zadanie::getZadanie($zapis->id_kursa);
                    $novosti = \backend\models\Newskurs::getNewskurs($zapis->id_kursa);
                    $kurs = \backend\models\Kurs::find()->where(['id_kurs' => $zapis->id_kursa])->one();
                    ?>

                    <div class="col-n2 col-lg-3 col-md-12 col-sm-12">
                        <div class="w-content">
                            <span class="user-page_heading__subtitle">Текущий курс:</span>
                            <span class="user-page_heading__course"><?= str_replace(' ', " <br> ", Html::encode($kurs->kursname))?></span>
                        </div>
                        <!-- /.w-content -->
                    </div>

                    <? endforeach; ?>

                </div>
            </div>
        </div>
        <!-- /.page-heading -->
    
        <div class="user-page_body">
            <div class="container">
                <div class="row">
                    <!--
                    RIGHT COLUMN ===============================================-->
                    <div class="col-lg-9 col-md-12 col-sm-12 col-xs-12 user-page_body__left-column">
                        <!-- Nav tabs -->
                        <div class="slim-scroll">
                            <ul class="nav nav-tabs user-page__tab-nav" role="tablist">
                                <li role="presentation" class="active"><a href="#tabMenu1" aria-controls="home" role="tab" data-toggle="tab">Домашние задания</a></li>
                                <li role="presentation"><a href="#tabMenu2" aria-controls="settings" role="tab" data-toggle="tab">Материалы</a></li>
                                <li role="presentation"><a href="#tabMenu3" id="commentLoad" data-uid="165" aria-controls="messages" role="tab" data-toggle="tab">Расписание</a></li>
                                <li role="presentation"><a href="#tabMenu4" aria-controls="files" role="tab" data-toggle="tab">Личные данные</a></li>
                            </ul>
                        </div>
    
                        <!-- Tab panes -->
                        <div class="w-tab-content">
                            <div class="back"></div>
                            <div class="tab-content user-page__tab-content">
    
                                <div role="tabpanel" class="tab-pane fade in active" id="tabMenu1">
    
                                    <!--------------------- SECTION 1 -------------------------->
                                    <div class="collapse-left">
                                        <?php $novosti = \backend\models\Newskurs::getNewskurs($zapis->id_kursa); ?>
                                        <? foreach ($zadaniya as $zadanie): ?>

                                            <div class="user-page__task-item">
                                                <span class="date"><?=  Yii::$app->formatter->asDate(strtotime($zadanie->date)); ?></span>
                                                <div class="task-item_heading"><h4><?= Html::encode($zadanie->name_zadanie)  ?></h4>

                                                        <span class="label"><?=\frontend\models\RequestZadanie::getStatus($zadanie->id)?></span>

                                                    <!-- /.label --></div>
                                                <p class="desc"><?= Html::encode($zadanie->description) ?> </p>
                                                <!-- /.desc -->
                                                <a data-id="<?= $zadanie->id ?>" href="#" class="user-page__more-link view-detail"><span class="arrow"></span>Подробнее</a>

                                                <div class="hide">
                                                    <div class="date-out"><?=  Yii::$app->formatter->asDate(strtotime($zadanie->date_out)); ?></div>
                                                    <div class="date"><?=  Yii::$app->formatter->asDate(strtotime($zadanie->date)); ?></div>
                                                    <div class="text"><?= ($zadanie->full_text) ?></div>
                                                </div>
                                            </div>
                                        <? endforeach; ?>
                                    </div>
                                    <!-- / .user-page__task-item-->

                                    <!---------------------END SECTION 1 -------------------------->
    
    
    
                                    <!---------------------CHANGE SECTION 2 -------------------------->
    
                                    <div class="detail-view hide-it">
                                        <section class="block-info">
                                            <div class="back-link"><a href="#" class="user-page__more-link back-to-list"><span class="arrow back-arrow"></span>Вернуться</a></div>
                                            <div class="date-text red">
                                                <span>Дата сдачи</span>
                                                <span class="date-out"></span>
                                            </div>
                                        </section>
                                        <article class="block-article">
                                            <div class="user-page__task-item">
                                                <span class="date create-date"></span>
                                                <div class="text"></div>
                                            </div>
                                        </article>
                                        <p><a data-id="0" style="padding: 10px; display: inline-block; text-align: center;margin-top: 25px;" href="#send-request" class="btn-default send-request-data">Сдать задание</a></p>
                                        <div class="content sendRequestZadanieForm">
                                            <hr>
                                            <h4>Cдача задания</h4>
                                            <div>
                                                <? $modelRequest = new \frontend\models\RequestZadanie(); ?>
                                                <?php $form = ActiveForm::begin([
                                                    'id' => 'form-request-zadanie',
                                                    'options' => [
                                                            'enctype' => 'multipart/form-data'
                                                    ]
                                                ]); ?>
                                                    <div class="form-group float-label-control">
                                                        <?= $form->field($modelRequest, 'description', [
                                                            'template' => "{input}{label}{hint}{error}"
                                                        ])->textarea(['class' => 'form-control']) ?>
                                                    </div>

                                                    <div class="hide">
                                                        <?= $form->field($modelRequest, 'id_zadanie')->hiddenInput(['value' => Yii::$app->request->get('id')]) ?>
                                                    </div>

                                                    <p>Файлы выполненой работы</p>
                                                    <!--progress id="progressbar" value="0" max="100"></progress-->
                                                    <!--div id="progressbar" role="progressbar" data-value="0" data-max="100"></div-->
                                                    <span id="uploaded-link" class="file-link drop-file-list">
                                                        <span class="file">

                                                        </span>
                                                    </span>
                                                    <div class="progress" style="display: none;">
                                                        <div id="progressbar" class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="1" aria-valuemin="1" aria-valuemax="100" style="width: 0%"></div>
                                                    </div>
                                                    <div class="drop-download-box">

                                                        <!--Drag&Drop download files-->
                                                        <!--V1-->
                                                        <div class="wrapper">

                                                            <div class="drop">
                                                                <div class="cont">
                                                                    <i class="fa cloud-upload-icon hidden-xs"></i>
                                                                    <span class="tit hidden-xs"> Перетащите файл или </span>
                                                                    <span id="mobile_file_upload" class="browse file_input_replacement">
                                                                         <span class="hidden-xs">нажмите сюда</span>
                                                                         <span class="xs-load-link visible-xs">Загрузите файл</span>
                                                                    </span>
                                                                    <!--input type="file" id="files" class="upload-file-input file_input_with_replacement" required multiple name="RequestZadanie[files_data][]" -->
                                                                    <?= $form->field($modelRequest, 'files_data',
                                                                        ['template' => "{input}{label}{hint}{error}"]
                                                                    )->fileInput(['id' => "files", 'class' => "upload-file-input file_input_with_replacement"])->label(false) ?>
                                                                </div>
                                                                <!--!!!Note программисту: Реализовать добавление/скрытие
                                                                следующих элементов по загрузке пользователем файла.-->
                                                                <output id="list" class="drop-file-list1 file-list1"> </output>
                                                            </div>

                                                            <div class="status"></div>
                                                        </div>
                                                    </div>
                                                    <p id="help-error-file" style="color: #a94442;"></p>
                                                    <!-- /.drop-download-box -->

                                                    <div class="btn-line">

                                                        <?= Html::submitButton(Yii::t('users', 'Сдать задание'), ['class' => 'btn btn-default', 'name' => 'signup-button']) ?>
                                                        <!-- /.btn btn-default -->
                                                    </div>
                                                <?php ActiveForm::end(); ?>
                                            </div>
                                        </div>

                                        <hr>
                                        <div class="historyLog loading">
                                            <h4>История задания</h4>
                                            <div class="data"></div>
                                            <div class="clear"></div>
                                        </div>
                                    </div>

                                    <div class="clear"></div>
    
    
                                    <!---------------------END SECTION 2 -------------------------->
                                </div>
                                <div role="tabpanel" class="tab-pane fade" id="tabMenu2">
                                    <div class="collapse-left">
                                    <?php $materials = \backend\models\Material::find()->where(['id_kursa' => $zapis->id_kursa])->orderBy('date DESC')->all(); ?>
                                    <?php foreach ($materials as $material): ?>
                                    <div data-id="<?=$material->id?>" class="user-page__task-item view-material-on-hover">
                                        <span class="date"><?=  Yii::$app->formatter->asDate(strtotime($material->date)); ?></span>
                                        <div class="task-item_heading"><h4><?= Html::encode($material->title) ?></h4>
                                            <? if(!\frontend\models\MaterialView::find()->where(['zadanie_id' => $material->id])->andWhere(['user_id' => Yii::$app->user->identity->id])->one()): ?>
                                            <span class="label">new</span>


                                            <?endif;?>
                                            <!-- /.label -->
                                        </div>
                                        <p class="desc"><?= ($material->description) ?> </p>
                                        <!-- /.desc -->
                                        <? if (!empty($material->description)):?>
                                            <a class="user-page__more-link view-detail-material" style="cursor:pointer"><span class="arrow"></span>Подробнее</a>
                                            <div class="hide">
                                                <div class="date-out"><?=  Yii::$app->formatter->asDate(strtotime($material->date)); ?></div>
                                                <div class="date"><?=  Yii::$app->formatter->asDate(strtotime($material->date)); ?></div>
                                                <div class="text"><?= ($material->full_text) ?></div>
                                            </div>
                                        <? endif;?>

                                        <? if (!empty($material->file_name)): ?>
                                        <p><span class="file-link"><i></i><?= Html::encode($material->file_name) ?></span></p>

                                        <?php echo '<a target="_blank" href="/files/material/' . $material->files . '" class="download-link">Скачать<span class="arrow"></span></a>';?>
                                        <!-- /.desc -->


                                        <? else: ?>
                                        <? endif; ?>

                                    </div>

                                    <?endforeach;?>
                                    </div>
                                    <!-- / .user-page__task-item-->

                                    <div class="detail-view hide-it">
                                        <section class="block-info">
                                            <div class="back-link"><a href="#" class="user-page__more-link back-to-list"><span class="arrow back-arrow"></span>Вернуться</a></div>
                                            <div class="date-text red">
                                                <span>Дата сдачи</span>
                                                <span class="date-out"></span>
                                            </div>
                                        </section>
                                        <article class="block-article">
                                            <div class="user-page__task-item">
                                                <span class="date create-date"></span>
                                                <div class="text"></div>
                                            </div>
                                        </article>
                                    </div>

                                    <div class="clear"></div>
    

                                    <!-- / .user-page__task-item-->
                                </div>
                                <?php  $prepod = \backend\models\Teacher::find()->where(['kurs' => $zapis->id_kursa])->andWhere(['teacher' => 1])->all();?>
                                <div role="tabpanel" class="schedule-tab tab-pane fade" id="tabMenu3">
                                    <div class="content-row user-page__theachers">
                                        <h4>Преподаватели</h4>
                                        <div class="w-details">
                                            <? foreach ($prepod as $teacher): ?>
                                            <div class="schedule-tab__table row">

                                                <div class="col-xs-12 col-sm-4 col-md-4">
                                                    <div class="title strrong"><?= Html::encode($teacher->username) ?></div>
                                                    <p><?= Html::encode($teacher->about) ?></p>
                                                </div>
                                                <div class="col-xs-12 col-sm-4 col-md-3">
                                                    <div class="title">Телефон</div>
                                                    <p><?= Html::encode($teacher->phone) ?></p>
                                                </div>
                                                <div class="col-xs-12 col-sm-4 col-md-5">
                                                    <div class="title">E-mail:</div>
                                                    <p><?= Html::encode($teacher->email) ?></p>
                                                </div>

                                            </div><br>
                                            <? endforeach; ?>
                                        </div>
                                        <!-- /.w-details -->
                                    </div>
                                    <!-- /.content-row -->
    
                                    <div class="content-row user-page__schedule">
                                        <h4>Расписание</h4>
                                        <div class="w-details">
                                            <div class="schedule-tab__table">
                                                <div class="tr">
                                                    <div class="td1"><?= ($kurs->raspisanie) ?></div>
<!--                                                    <div class="td">День недели</div>-->
                                                </div>
                                                <!-- /.tr -->
<!--                                                <div class="tr">-->
<!--                                                    <div class="td">13:00–15:00</div>-->
<!--                                                    <div class="td">День недели</div>-->
<!--                                                </div>-->
                                                <!-- /.tr -->
<!--                                                <div class="tr">-->
<!--                                                    <div class="td">13:00–15:00</div>-->
<!--                                                    <div class="td">День недели</div>-->
<!--                                                </div>-->
                                                <!-- /.tr -->
                                            </div>
                                        </div>
                                        <!-- /.w-details -->
                                    </div>
                                    <!-- /.content-row -->
    
                                    <div class="content-row user-page__weekends">
                                        <div class="w-heading">
                                            <span class="icon"></span><h4>Выходные дни</h4>
                                        </div>
                                        <!-- /.w-heading -->
    
                                        <div class="w-details">
                                            <div class="schedule-tab__table">
                                                <div class="tr">
                                                    <div class="td1"><?= ($kurs->vuxodnie) ?></div>
<!--                                                    <div class="td">День Конcтитуции Украины</div>-->
                                                </div>
                                                <!-- /.tr -->
<!--                                                <div class="tr">-->
<!--                                                    <div class="td">24 августа</div>-->
<!--                                                    <div class="td">День незaвисимости Украины</div>-->
<!--                                                </div>-->
                                                <!-- /.tr -->
                                            </div>
                                        </div>
                                        <!-- /.w-details -->
                                    </div>
                                    <!-- /.content-row -->
                                </div>
                                <div role="tabpanel" class="tab-pane fade" id="tabMenu4">
                                    <div class="content-row user-page__theachers">
                                        <section class="register-form">
                                            <?php $form = ActiveForm::begin(['id' => 'form-profile']); ?>
                                                <h4>Пользователь</h4>
                                                <div class="row">
                                                    <div class="col-xs-12 col-sm-6">
                                                        <div class="form-group float-label-control">

                                                        <?= $form->field($model, 'username', [
                                                                'template' => "{input}{label}{hint}{error}"
                                                        ])->textInput(['placeholder' => 'Имя и фамилия', 'class' => 'form-control empty','data-error' => 'Сообщение об ошибке' ]) ?>

                                                        </div>



<!--                                                        <div class="form-group float-label-control">-->
<!--                                                            <input type="email" id="inputName" class="form-control empty" placeholder="Имя и фамилия"-->
<!--                                                                   data-error="Сообщение об ошибке" required>-->
<!--                                                            <label for="inputEmail">Имя и фамилия</label>-->
<!--                                                            <div class="help-block with-errors"></div>-->
<!--                                                        </div>-->
                                                    </div>
                                                    <div class="col-xs-12 col-sm-6">
                                                        <div class="form-group float-label-control">
                                                        <?= $form->field($model, 'email', [
                                                            'template' => "{input}{label}{hint}{error}"
                                                        ])->textInput(['placeholder' => 'Эл. почта', 'class' => 'form-control empty','data-error' => 'Сообщение об ошибке' ]) ?>

<!--                                                        <div class="form-group float-label-control">-->
<!--                                                            <input type="email" id="inputEmail" class="form-control empty" placeholder="Эл. почта"-->
<!--                                                                   data-error="Сообщение об ошибке" required>-->
<!--                                                            <label for="inputEmail">Эл. почта</label>-->
<!--                                                            <div class="help-block with-errors"></div>-->
                                                        </div>
                                                        <div class="soc-auth">
                                                            <h3> Авторизация через соц сети</h3>
                                                            <?= AuthKeysManager::widget([
                                                                'baseAuthUrl' => ['/user/auth/index'],
                                                            ]) ?></div>
                                                    </div>

                                                </div>
                                                <div class="row">
                                                    <div class="col-xs-12 col-sm-6">
                                                        <div class="form-group float-label-control">
                                                            <?= $form->field($model, 'phone', [
                                                                'template' => "{input}{label}{hint}{error}"
                                                            ])->textInput(['placeholder' => 'Телефон', 'class' => 'form-control empty phone-top','data-error' => 'Сообщение об ошибке' ]) ?>
<!--                                                            <input type="tel" id="inputPhone" class="form-control" placeholder="Телефон"-->
<!--                                                                   data-error="Сообщение об ошибке">-->
<!--                                                            <label for="inputPhone">Телефон</label>-->
                                                            <div class="help-block with-errors"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <?= Html::submitButton(Yii::t('users', 'Сохранить'), ['class' => 'btn btn-default', 'name' => 'profile-button']) ?>
                                                </div><br><br>
                                            <?php ActiveForm::end(); ?>
                                                <h4>Изменить пароль</h4>
                                            <?php $form = ActiveForm::begin(['id' => 'form-password']); ?>
                                                <div class="row">

                                                    <?php if ($model->password_hash != '') : ?>
                                                    <div class="col-xs-12 col-sm-6">


                                                        <div class="panel-body-no">
                                                            <div class="form-group float-label-control">

                                                                <?= $form->field($changePasswordForm, 'old_password', [
                                                                        'template' => "{input}{label}{hint}{error}"
                                                                ])->passwordInput(['placeholder' => '', 'class' => 'form-control empty', 'data-error' => 'Сообщение об ошибке', 'required' => 'required']); ?>

                                                            </div>
                                                        </div>


<!--                                                        <div class="form-group float-label-control">-->
<!--                                                            <input type="password" id="inputPass" class="form-control empty" maxlength="25"-->
<!--                                                                   placeholder="Текущий пароль" data-error="Сообщение об ошибке" required>-->
<!--                                                            <label>Текущий пароль</label>-->
<!--                                                            <div class="help-block with-errors"></div>-->
<!--                                                        </div>-->
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-xs-12 col-sm-6">
                                                        <div class="form-group float-label-control">
<!--                                                            <input type="password" id="inputPassNew" class="form-control empty input-eye" maxlength="25"-->
<!--                                                                   placeholder="Новый пароль" data-error="Сообщение об ошибке" required>-->
                                                            <?= $form->field($changePasswordForm, 'new_password',[
                                                                'template' => "{input}{label}{hint}{error}"
                                                            ])->passwordInput(['placeholder' => '', 'class' => 'form-control empty', 'data-error' => 'Сообщение об ошибке', 'required' => 'required']); ?>
<!--                                                            <label>Новый пароль</label>-->
                                                            <div class="help-block with-errors"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-xs-12 col-sm-6">
                                                        <div class="form-group float-label-control">
                                                            <?= $form->field($changePasswordForm, 'new_password_repeat',[
                                                                'template' => "{input}{label}{hint}{error}"
                                                            ])->passwordInput(['placeholder' => '', 'class' => 'form-control empty', 'data-error' => 'Сообщение об ошибке', 'required' => 'required']); ?>
<!--                                                            <input type="password" id="inputPassRepeat" class="form-control empty" maxlength="25"-->
<!--                                                                   placeholder="Повторите новый пароль" data-error="Сообщение об ошибке" required>-->
<!--                                                            <label>Повторите новый пароль</label>-->
                                                            <div class="help-block with-errors"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <?= Html::submitButton(Yii::t('users', 'Сохранить'), ['class' => 'btn btn-default', 'name' => 'password-button']) ?>
                                                </div>
                                            <?php ActiveForm::end(); ?>

                                        </section>
                                    </div>
                                    <!-- /.content-row -->
                                </div>      <?php endif;?>
    
                            </div>
                        </div>
                        <!-- /.w-tab-content -->
    
                    </div>
    
                    <!--
                    LEFT BAR ===============================================-->
                    <div class="col-lg-3 col-md-12 col-sm-12 col-xs-12 user-page_body__right-column">
                        <div class="row">
                            <hr class="visible-xs">
                            <div class="panel">
                                <div class="panel-heading"><div class="h4">Новости</div></div>
                                <!-- /.panel-heading -->
                                <div class="panel-body">

                                    <? foreach ($novosti as $novost): ?>

                                    <div class="mini-news-item">
                                        <span class="mini-news-item__date"><?= strftime('%d %B %Y', strtotime($novost->date)); ?></span>
                                        <div class="mini-news-item__title">
                                            <?php if (($novost->status) == 1): ?>
                                            <div class="mini-news-item__title warning-news">
                                                <?php endif; ?>
                                              <?php $title = mb_strimwidth($novost->text, 0, 300, " "); ?>
                                            <?= ($title) ?>
                                           <!--  <?= Html::encode($novost->title) ?> -->
                                        </div>
                                    </div>
                                    <? endforeach; ?>
    
<!--                                    <div class="mini-news-item">-->
<!--                                        <span class="mini-news-item__date">12 Августа 2017</span>-->
<!--                                        <div class="mini-news-item__title">-->
<!--                                            <div class="mini-news-item__title warning-news">-->
<!--                                            Приведите на курс друга и получите скидку 20% на следующий месяц-->
<!--                                        </div>-->
<!--                                    </div>-->
<!--                                    <div class="mini-news-item">-->
<!--                                        <span class="mini-news-item__date">12 Августа 2017</span>-->
<!--                                        <div class="mini-news-item__title">-->
<!--                                            Внимание! Занятие переносится-->
<!--                                        </div>-->
<!--                                    </div>-->
<!--                                    <div class="mini-news-item">-->
<!--                                        <span class="mini-news-item__date">12 Августа 2017</span>-->
<!--                                        <div class="mini-news-item__title">-->
<!--                                            Поздравляем, Вы стали участником курса Front-End Development-->
<!--                                        </div>-->
<!--                                    </div>-->
                                </div>
                                <!-- /.panel-body -->
                            </div>
                            <!-- /.panel -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.page-body -->
    </div>